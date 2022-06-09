<?php

namespace Modules\Tax\Services;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Redis;
use Modules\Core\Facades\CoreCache;
use Modules\Core\Facades\SiteConfig;
use Modules\GeoIp\Facades\GeoIp;
use Modules\GeoIp\Traits\HasClientIp;
use Modules\Tax\Facades\TaxCache;
use Illuminate\Support\Str;
use Modules\Tax\Entities\TaxRule;

class NewTaxPrice {

    use HasClientIp;

    protected $total_tax_rate = 0;
    protected array $multiple_rules;

    public function getConfigValue(object $request): object
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
                "check_tax_catalog_prices" => SiteConfig::fetch("tax_catalog_prices", "channel", $channel?->id) ?? 1,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (object) $data;
    }

    public function getCountryAndZipCode(object $config_data, ?int $country_id = null, ?string $zip_code = null): array
    {
        try
        {
            $fetched = [];
            if (!$country_id) {
                $current_geo_location = GeoIp::locate(GeoIp::requestIp());
                $allow_countries = $config_data->allow_countries->pluck("iso_2_code")->toArray();

                if (!in_array($current_geo_location?->iso_code, $allow_countries)) {
                    $country = $config_data->default_country;
                    $zip_code = $zip_code ?? "*";
                } else {
                    $country = TaxCache::country()->where("iso_2_code", $current_geo_location?->iso_code)->first();
                    $zip_code = $current_geo_location->postal_code ?? "*";
                }
            } else {
                $country = TaxCache::country()->where("id", $country_id)->first();
                if (!$country) {
                    $country = $config_data->default_country;
                }
                $zip_code = $zip_code ?? "*";
            }

            $fetched = [
                "country" => $country,
                "zip_code" => $zip_code
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getTaxRulesWithRateInList(object $country, string $zip_code, ?int $customer_tax_group_id = 1): array
    {
        try
        {
            $all_tax_rules = [];
            if ($customer_tax_group_id) {
                $customer_tax_group = TaxCache::customerTaxGroup()->where("id", $customer_tax_group_id)->first();
            }

            if (isset($customer_tax_group)) {

                $tax_rules = $customer_tax_group->tax_rules()->orderBy("priority")->get();

                foreach($tax_rules as &$tax_rule) {
                    $tax_rates = $this->getTaxRate($tax_rule, $country, $zip_code, 1);
                    if ($tax_rates->isEmpty()) {
                        continue;
                    }

                    $tax_rule->tax_rates = $tax_rates;
                    $all_tax_rules[] = $tax_rule;
                }
            }

        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $all_tax_rules;
    }

    public function calculateForList(mixed $price, array $tax_data, ?int $product_tax_group_id = null): object
    {
        try
        {
            $this->total_tax_rate = 0;
            $this->multiple_rules = [];
            $tax_rates = [];

            if ($product_tax_group_id) {
                $product_tax_group = TaxCache::productTaxGroup()->where("id", $product_tax_group_id)->first();
            }
            if (isset($product_tax_group)) {
                foreach($tax_data["tax_rules"] as $tax_rule) {
                    $product_tax_group_ids = $tax_rule->product_tax_groups->pluck("id")->toArray();
                    if (!in_array($product_tax_group_id, $product_tax_group_ids)) {
                        continue;
                    }
                    $tax_rates[] = $tax_rule->tax_rates->sortByDesc("tax_rate")->first();
                }
            }

            $tax_details = $this->processTaxRates($tax_rates, $tax_data["config_data"], $price);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $this->hydrate($tax_details);
    }

    private function getTaxRate(
        object $tax_rule,
        object $country,
        string $zip_code,
        ?int $listStatus = null
    ): ?object {
        try
        {
            $tax_rate = $tax_rule->tax_rates
            ->where("country_id", $country->id)
            ->filter(function ($tax_rate) use ($zip_code) {
                if ($zip_code) {
                    if ($tax_rate->use_zip_range) {
                        $zip_code_range = range($tax_rate->postal_code_form, $tax_rate->postal_code_to);
                        return in_array($zip_code, $zip_code_range);
                    } else {
                        if ($tax_rate->zip_code == "*") {
                            return true;
                        }
                        if ( Str::contains($tax_rate->zip_code, "*") ) {
                            $pluck_range = explode("*", $tax_rate->zip_code);
                            $str_count = Str::length($pluck_range[0]);
                            $zip_code_prefix = substr($zip_code, 0, $str_count);
                            return ($pluck_range[0] == $zip_code_prefix);
                        }
                    }
                }
                return true;
            });
            if (!$listStatus) {
                $tax_rate = $tax_rate->sortByDesc("tax_rate")->first();
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $tax_rate;
    }


    public function calculate(
        object $request,
        mixed $price,
        ?int $product_tax_group_id = null,
        ?int $customer_tax_group_id = 1,
        ?int $country_id = null,
        ?string $zip_code = null,
        ?callable $callback = null
    ): object {
        try
        {
            $this->total_tax_rate = 0;
            $this->multiple_rules = [];

            $config_data = $this->getConfigValue($request);

            $country_data = $this->getCountryAndZipCode($config_data, $country_id, $zip_code);
            $country = $country_data["country"];
            $zip_code = $country_data["zip_code"];

            $tax_rates = $this->filterTaxRates($country, $config_data, $product_tax_group_id, $customer_tax_group_id, $zip_code);
            $tax_details = $this->processTaxRates($tax_rates, $config_data, $price);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $this->hydrate($tax_details);
    }

    public function filterTaxRates(
        object $country,
        object $config_data,
        ?int $product_tax_group_id,
        ?int $customer_tax_group_id,
        ?string $zip_code = null
    ): array {
        try
        {
            if ($product_tax_group_id) {
                $product_tax_group = TaxCache::productTaxGroup()->where("id", $product_tax_group_id)->first();
            }
            if ($customer_tax_group_id) {
                $customer_tax_group = TaxCache::customerTaxGroup()->where("id", $customer_tax_group_id)->first();
            }

            if (!isset($product_tax_group) || !isset($customer_tax_group)) {
                return [];
            }

            $cache_name = "taxCache-product-{$product_tax_group_id}_customer-{$customer_tax_group_id}_channel-{$config_data->channel->id}_country-{$country->id}_zipcode-{$zip_code}";
            $tax_rates = json_decode(Redis::get($cache_name));

            if (!$tax_rates) {
                $tax_rates = $this->getTaxRulesWithRate($product_tax_group, $customer_tax_group, $country, $zip_code);
                Redis::set($cache_name, json_encode($tax_rates));
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $tax_rates;
    }

    private function getTaxRulesWithRate(
        object $product_tax_group,
        object $customer_tax_group,
        object $country,
        string $zip_code
    ): array {
        try
        {
            $tax_rates = [];
            $tax_rules = $product_tax_group->tax_rules()->whereHas('customer_tax_groups', function (Builder $query) use ($customer_tax_group) {
                $query->where('customer_tax_group_id', $customer_tax_group->id);
            })->orderBy("priority")->get();

            foreach($tax_rules as $tax_rule) {
                $tax_rate = $this->getTaxRate($tax_rule, $country, $zip_code);
                if ($tax_rate) {
                    $tax_rate->rule_priority = $tax_rule->priority;
                    $tax_rate->rule_id = $tax_rule->id;
                    $tax_rates[] = $tax_rate;
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $tax_rates;
    }

    private function processTaxRates(array $tax_rates, object $config_data, mixed $price): array
    {
        try
        {
            $calculate_data = (count($tax_rates) > 0) ? $this->calculateTaxRates($tax_rates, $config_data, $price) : [ "price" => $price ?? 0, "final_tax" => 0 ];
            $resource = $this->taxResource($calculate_data);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $resource;
    }

    private function calculateTaxRates(array $tax_rates, object $config_data, mixed $price): array
    {
        try
        {
            $calculate_data = [];
            $final_tax = $this->applyRules($tax_rates, $config_data, $price);
            if ($config_data->check_tax_catalog_prices == 2) {
                $price = $price/($final_tax + 1);
                $update_config_data = clone $config_data;
                $update_config_data->check_tax_catalog_prices = 1;
                $final_tax = $this->applyRules($tax_rates, $update_config_data, $price);
            }

            $calculate_data = [
                "price" => $price ?? 0,
                "final_tax" => $final_tax ?? 0,
            ];

        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $calculate_data;
    }

    private function applyRules(array $tax_rates, object $config_data, mixed $price): mixed
    {
        try
        {
            $duplicate_priority_check = [];
            $previous_taxes = 0;
            $total_tax = 0;
            $prev_taxes = [];

            $this->total_tax_rate = 0;
            $this->multiple_rules = [];

            foreach($tax_rates as $i => $tax_rate) {
                $this->total_tax_rate += $tax_rate->tax_rate;

                if ($i == 0 || in_array($tax_rate->rule_priority, $duplicate_priority_check)) {
                    $duplicate_priority_check[] = $tax_rate->rule_priority;
                    if ($config_data->check_tax_catalog_prices == 1) {
                        $computed_tax = $price * ($tax_rate->tax_rate/100);
                        $previous_taxes += $computed_tax;
                    } else $computed_tax = $tax_rate->tax_rate/100;
                } else {
                    if ($config_data->check_tax_catalog_prices == 1) {

                        $compound_price = $price + $previous_taxes;
                        $computed_tax = $compound_price * ($tax_rate->tax_rate/100);

                        $previous_taxes += $price * ($tax_rate->tax_rate/100);
                    } else {
                        foreach($prev_taxes as $prev_tax) {
                            $total_tax += $prev_tax * ($tax_rate->tax_rate/100);
                        }
                        $computed_tax = ($tax_rate->tax_rate/100);
                    }
                }
                $total_tax += $computed_tax;
                $prev_taxes[] = $tax_rate->tax_rate/100;

                if ($config_data->check_tax_catalog_prices == 1) {
                    $rule = TaxCache::taxRule()->where("id", $tax_rate->rule_id)->first()?->toArray();
                    unset($rule["tax_rates"], $rule["customer_tax_groups"], $rule["product_tax_groups"], $rule["created_at"], $rule["updated_at"]);
                    $rule["rates"][] = (object) [
                        "id" => $tax_rate->id,
                        "identifier" => $tax_rate->identifier,
                        "tax_rate" => $tax_rate->tax_rate,
                        "tax_rate_value" => $computed_tax,
                    ];
                    $rule["rates"] = collect($rule["rates"]);
                    $this->multiple_rules[] = (object) $rule;
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $total_tax;
    }

    public function taxResource(array $calculate_data, ?callable $callback = null): array
    {
        try
        {
            $resource = [
                "price" => round($calculate_data["price"], 2),
                "tax_rate_percent" => $this->total_tax_rate,
                "tax_rate_value" => round($calculate_data["final_tax"], 2),
                "rules" => collect($this->multiple_rules),
            ];
            if ($callback) {
                $resource = array_merge($callback(), $resource);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $resource;
    }

    public function hydrate(array $attributes = []): mixed
    {
        try
        {
            $resources = new TaxAttributes($attributes);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $resources;
    }

}
