<?php

namespace Modules\Tax\Services;

use Exception;
use Modules\Core\Facades\CoreCache;
use Modules\Core\Facades\SiteConfig;
use Illuminate\Support\Str;
use Modules\GeoIp\Facades\GeoIp;
use Modules\GeoIp\Traits\HasClientIp;
use Modules\Tax\Facades\TaxCache;

class TaxPrice {

    use HasClientIp;

    protected $country_code,
    $zip_code,
    $net_price = 0.0,
    $tax_value = 0,
    $tax_rate = 0,
    $priority_tax = [],
    $company = false,
    $value = 0,
    $multiple_rules,
    $country_id;

    public function hydrate(array $attributes = []): mixed
    {
        return new TaxAttributes($attributes);
    }
    
    public function get(object $request, bool $use_current_location = false, ?string $zip_code = null, ?callable $callback = null): object
    {
        try
        {
            $data = $this->getGeneralValue($request);
            $current_geo_location = GeoIp::locate($this->requestIp());
            if ($use_current_location) {
                if (!in_array($current_geo_location?->iso_code, $data->allow_countries->pluck("iso_2_code")->toArray())) $country = $data->default_country;
                else $country = TaxCache::country()->where("iso_2_code", $current_geo_location?->iso_code)->first();
                $zip_code = $current_geo_location?->postal_code;
            }
            else $country = $data->default_country;

            $tax_rate_data = TaxCache::taxRate()->where("country_id", $country->id)->filter(function ($tax_rate) use ($zip_code) {
                if ($zip_code) {
                    if ($tax_rate->use_zip_range) {
                        $zip_code_range = range($tax_rate->postal_code_form, $tax_rate->postal_code_to);
                        return in_array($zip_code, $zip_code_range);
                    }
                    else {
                        if ($tax_rate->zip_code == "*") return true;
                        if ( Str::contains($tax_rate->zip_code, "*") ) {
                            $pluck_range = explode("*", $tax_rate->zip_code);
                            $str_count = Str::length($pluck_range[0]);
                            $zip_code_prefix = substr($zip_code, 0, $str_count);
                            return ($pluck_range[0] == $zip_code_prefix);
                        }
                    }
                }
                return true;
            })->first();
    
            $tax_rate_values = [
                "iso_code" => $country->iso_2_code,
                "country_name" => $country->name,
                "tax_identifier" => $tax_rate_data->identifier,
                "tax_rate" => $tax_rate_data->tax_rate,
                "client_zip_code" => $current_geo_location?->postal_code,
                "client_country_name" => $current_geo_location?->country,
                "client_iso_code" => $current_geo_location?->iso_code,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (object) $tax_rate_values;
    }

    public function taxResource(mixed $price, mixed $tax, ?object $channel, ?callable $callback = null): array
    {
        $tax_rate_value = ($price * $tax);
        $resource = [
            "price" => $price,
            "tax_rate_percent" => $this->tax_value,
            "tax_rate_value" => $tax_rate_value,
            "rules" => $this->getMultipleRules($price),
        ];
        if ($callback) $resource = array_merge($callback, $resource); 
        $this->tax_value = 0;
        return $resource;
    }

    public function getMultipleRules(mixed $price): ?object
    {
        try
        {
            $data = $this->multiple_rules?->map(function ($rule) use ($price) {
                return (object) [
                    "id" => $rule->id,
                    "name" => $rule->name,
                    "rates" => $rule->tax_rates?->where("country_id", $this->country_id)
                    ->filter(function ($tax_rate) {
                        if ($this->zip_code) {
                            if ($tax_rate->use_zip_range) {
                                $zip_code_range = range($tax_rate->postal_code_form, $tax_rate->postal_code_to);
                                return in_array($this->zip_code, $zip_code_range);
                            }
                            else {
                                if ($tax_rate->zip_code == "*") return true;
                                if ( Str::contains($tax_rate->zip_code, "*") ) {
                                    $pluck_range = explode("*", $tax_rate->zip_code);
                                    $str_count = Str::length($pluck_range[0]);
                                    $zip_code_prefix = substr($this->zip_code, 0, $str_count);
                                    return ($pluck_range[0] == $zip_code_prefix);
                                }
                            }
                        }
                        return true;
                    })->map(function ($rate) use ($price) {
                        $value_added_tax = ($rate->tax_rate / 100) * $price;
                        return (object) [
                            "id" => $rate->id,
                            "identifier" => $rate->identifier,
                            "tax_rate" => $rate->tax_rate,
                            "tax_rate_value" => $value_added_tax
                        ];
                    })
                ];
            })->values();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (object) $data;
    }

    public function getGeneralValue(object $request): object
    {
        try
        {
            $website = CoreCache::getWebsite($request->header("hc-host"));
            $channel = CoreCache::getChannel($website, $request->header("hc-channel"));
            $store = CoreCache::getStore($website, $channel, $request->header("hc-store"));
    
            $data = [
                "website" => $website,
                "channel" => $channel,
                "store" => $store,
                "allow_countries" => SiteConfig::fetch("allow_countries", "channel", $channel?->id),
                "default_country" => SiteConfig::fetch("default_country", "channel", $channel?->id),
                "check_tax_catalog_prices" => SiteConfig::fetch("tax_catalog_prices", "channel", $channel?->id)
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (object) $data;
    }

    public function calculate(object $request, mixed $price, ?int $product_tax_group_id = null, ?int $customer_tax_group_id = null, bool $use_current_location = false, ?string $zip_code = null, ?callable $callback = null)
    {
        $data = $this->getGeneralValue($request);
        
        if ($data->check_tax_catalog_prices) {
            $tax = $this->taxRate($request, $product_tax_group_id, $customer_tax_group_id, $use_current_location, $zip_code);
            if ($callback) $tax = $callback;
            $over_all_tax_price = $this->taxResource($price, $tax, $data->channel);
        }
        else $over_all_tax_price = $this->taxResource($price, 0, $data->channel);
        
        return $this->hydrate($over_all_tax_price);
    }

    public function taxRate(object $request, ?int $product_tax_group_id = null, ?int $customer_tax_group_id = null, bool $use_current_location = false, ?string $zip_code = null): mixed
    {
        try
        {
            $this->value = 0;
            $this->tax_rate = 0;
            
            $data = $this->getGeneralValue($request);
            $current_geo_location = GeoIp::locate($this->requestIp());
            if ($use_current_location) {
                if (!in_array($current_geo_location?->iso_code, $data->allow_countries->pluck("iso_2_code")->toArray())) $country = $data->default_country;
                else $country = TaxCache::country()->where("iso_2_code", $current_geo_location?->iso_code)->first();
                $zip_code = $current_geo_location?->postal_code; 
            }
            else $country = $data->default_country;
            if ($product_tax_group_id) {
                $tax_group = TaxCache::productTaxGroup()->where("id", $product_tax_group_id)->first();
            }
            else {
                $tax_group = TaxCache::customerTaxGroup()->where("id", $customer_tax_group_id)->first();
            }
            if (!$tax_group) return $this->tax_rate;
            $sort_priority_tax_rule = $tax_group->tax_rules->pluck("priority", "id")->toArray();
            if(!empty($sort_priority_tax_rule)) {
                if (!empty(array_not_unique($sort_priority_tax_rule)["duplicate_array"])) {
                    $same_tax_rule_ids = array_keys(array_not_unique($sort_priority_tax_rule)["duplicate_array"]);	
                    $same_priority_tax_rate = $this->getPriorityTaxRate($same_tax_rule_ids, $country, $data->allow_countries, $zip_code)?->pluck("tax_rate")->toArray();
                    $this->tax_value = array_sum($same_priority_tax_rate);
                }
                else {
                    $unique_tax_rule_priorities = array_not_unique($sort_priority_tax_rule)["unique_array"];
                    $priority_tax_rate = $this->getPriorityTaxRate(array_flip(array_min($unique_tax_rule_priorities)), $country, $data->allow_countries, $zip_code)?->pluck("tax_rate")->toArray();
                    $this->tax_value += array_sum($priority_tax_rate);
                }
            }
            
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        
        return $this->tax_rate = ($this->tax_value / 100);
    }

    private function getPriorityTaxRate(array $tax_rule_ids, object $country, mixed $allow_countries, ?string $zip_code = null): mixed
    {
        try
        {
            $tax_rules = TaxCache::taxRule()->whereIn("id", $tax_rule_ids);
            $this->multiple_rules = $tax_rules;
            $this->country_id = $country->id;
            $this->zip_code = $zip_code;
            $tax_rules_data = $tax_rules->map(function ($tax_rule) use ($country, $zip_code, $allow_countries) {
                return $tax_rule->tax_rates
                ->where("country_id", $country->id)
                ->filter(function ($tax_rate) use ($zip_code) {
                    if ($zip_code) {
                        if ($tax_rate->use_zip_range) {
                            $zip_code_range = range($tax_rate->postal_code_form, $tax_rate->postal_code_to);
                            return in_array($zip_code, $zip_code_range);
                        }
                        else {
                            if ($tax_rate->zip_code == "*") return true;
                            if ( Str::contains($tax_rate->zip_code, "*") ) {
                                $pluck_range = explode("*", $tax_rate->zip_code);
                                $str_count = Str::length($pluck_range[0]);
                                $zip_code_prefix = substr($zip_code, 0, $str_count);
                                return ($pluck_range[0] == $zip_code_prefix);
                            }
                        }
                    }
                    return true;
                })->first();
            });	
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        
        return $tax_rules_data;
    }

}
