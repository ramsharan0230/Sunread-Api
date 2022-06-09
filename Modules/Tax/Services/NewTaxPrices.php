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
use Modules\Core\Facades\PriceFormat;
use Modules\Core\Services\Pipe;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomerGroup;
use Modules\Product\Repositories\ProductBaseRepository;

class NewTaxPrices {

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

    public function getCountryAndZipCode(object $config_data, ?int $country_id = null, ?string $zip_code = null, ?int $region_id = null): array
    {
        try
        {
            $fetched = [];
            if (!$country_id) {
                $current_geo_location = GeoIp::locate(GeoIp::requestIp());
                $allow_countries = $config_data->allow_countries->pluck("iso_2_code")->toArray();
                $region_id = null;

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
                "region_id" => $region_id,
                "zip_code" => $zip_code,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    private function getTaxRate(
        object $tax_rule,
        object $country,
        string $zip_code,
        ?int $region,
        ?int $listStatus = null
    ): ?object {
        try
        {
            $taken = new Pipe($tax_rule);
            $tax_rate = $taken->pipe($taken->value->tax_rates)
                ->pipe($taken->value->where("country_id", $country->id))
                ->pipe($this->filterCountries($taken->value, $zip_code))
                ->value;

            if ($region) {
                $region_tax_rate = (clone $tax_rate)->where("region_id", $region);
            }

            $final_tax_rate = (isset($region_tax_rate) && !$region_tax_rate->isEmpty()) ? $region_tax_rate : $tax_rate;

            if (!$listStatus) {
                $final_tax_rate = $final_tax_rate->sortByDesc("tax_rate")->first();
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $final_tax_rate;
    }

    public function getCloneRegionTaxRate(object $tax_rate): object
    {
        return clone $tax_rate;
    }


    public function calculate(
        object $request,
        int $product_id,
        ?int $country_id = null,
        ?string $zip_code = null,
        ?int $region_id = null,
        ?callable $callback = null
    ): array {
        try
        {
            $fetched = [];
            $this->total_tax_rate = 0;
            $this->multiple_rules = [];

            $config_data = $this->getConfigValue($request);

            $country_data = $this->getCountryAndZipCode($config_data, $country_id, $zip_code, $region_id);
            $country = $country_data["country"];
            $region = $country_data["region_id"];
            $zip_code = $country_data["zip_code"];

            $product_data = $this->getProductData($product_id, $config_data->store);
            $product_tax_group_id = $product_data["tax_class"];
            unset($product_data["tax_class"]);
            $prices = $product_data;

            $tax_rates = $this->filterTaxRates(
                country: $country,
                config_data: $config_data,
                product_tax_group_id: $product_tax_group_id,
                zip_code: $zip_code,
                region: $region
            );

            foreach ($prices as $key => $price) {
                if ($price == 0) {
                    $fetched[$key] = $this->taxResource($config_data, [], function () use ($price, $key) {
                        if(($key == "special") && ($price == 0)) {
                            return [
                                "amount" => null,
                                "amount_formatted" => null,
                            ];
                        }
                        return [];
                    });
                } else {
                    $fetched[$key] = $this->processTaxRates($tax_rates, $config_data, $price, $key);
                }
                // $fetched[$key] = $this->hydrate($tax_details);
            }

            $fetched["final"] = isset($fetched["special"]["amount"]) ? $fetched["special"] : $fetched["regular"];
            unset($fetched["regular"]["rules"], $fetched["special"]["rules"]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function filterTaxRates(
        object $country,
        object $config_data,
        ?int $product_tax_group_id,
        ?string $zip_code = null,
        ?int $region = null
    ): array {
        try
        {
            if ($product_tax_group_id) {
                $product_tax_group = TaxCache::productTaxGroup()->where("id", $product_tax_group_id)->first();
            }

            $customer = Customer::whereId(auth("customer")->id())->with(["group.tax_group"])->first();
            if ($customer) {
                $customer_tax_group_id = $customer?->group?->tax_group?->id;
            } else {
                $customer_group = CustomerGroup::whereSlug("not_logged_in")->first();
                $customer_tax_group_id = $customer_group?->tax_group?->id;
            }

            if ($customer_tax_group_id) {
                $customer_tax_group = TaxCache::customerTaxGroup()->where("id", $customer_tax_group_id)->first();
            }

            if (!isset($product_tax_group) || !isset($customer_tax_group)) {
                return [];
            }

            $region_cache = $region ?? 0;
            $cache_name = "taxCache-product-{$product_tax_group_id}_customer-{$customer_tax_group_id}_channel-{$config_data->channel->id}_country-{$country->id}_region-{$region_cache}_zipcode-{$zip_code}";
            $tax_rates = json_decode(Redis::get($cache_name));

            if (!$tax_rates) {
                $tax_rates = $this->getTaxRulesWithRate($product_tax_group, $customer_tax_group, $country, $zip_code, $region);
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
        string $zip_code,
        ?int $region
    ): array {
        try
        {
            $tax_rates = [];
            $taken = new Pipe($product_tax_group);
            $tax_rules = $taken->pipe($taken->value->tax_rules())
                ->pipe($this->filterCustomerTaxGroups($taken->value, $customer_tax_group))
                ->pipe($taken->value->orderBy("priority"))
                ->pipe($taken->value->get())
                ->value;

            foreach ($tax_rules as $tax_rule) {
                $tax_rate = $this->getTaxRate($tax_rule, $country, $zip_code, $region);
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

    private function processTaxRates(array $tax_rates, object $config_data, mixed $price, string $key): array
    {
        try
        {
            $calculate_data = (count($tax_rates) > 0)
                ? $this->calculateTaxRates($tax_rates, $config_data, $price)
                : [
                    "price" => $price ?? 0,
                    "final_tax" => 0,
                ];
            $resource = $this->taxResource($config_data, $calculate_data, function () use ($price, $key) {
                if (($key == "special") && ($price == 0)) {
                    return [
                        "amount" => null,
                        "amount_formatted" => null,
                    ];
                }
                return [];
            });
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

            foreach ($tax_rates as $key => $tax_rate) {
                $this->total_tax_rate += $tax_rate->tax_rate;

                if ($key == 0 || in_array($tax_rate->rule_priority, $duplicate_priority_check)) {
                    $duplicate_priority_check[] = $tax_rate->rule_priority;
                    if ($config_data->check_tax_catalog_prices == 1) {
                        $computed_tax = $price * ($tax_rate->tax_rate/100);
                        $previous_taxes += $computed_tax;
                    } else {
                        $computed_tax = $tax_rate->tax_rate/100;
                    }
                } else {
                    if ($config_data->check_tax_catalog_prices == 1) {

                        $compound_price = $price + $previous_taxes;
                        $computed_tax = $compound_price * ($tax_rate->tax_rate/100);

                        $previous_taxes += $price * ($tax_rate->tax_rate/100);
                    } else {
                        foreach ($prev_taxes as $prev_tax) {
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

    public function taxResource(object $config_data, array $calculate_data = [], ?callable $callback = null): array
    {
        try
        {
            $amount = isset($calculate_data["price"]) ? round($calculate_data["price"], 2) : 0;
            $tax_amount = isset($calculate_data["final_tax"]) ? round($calculate_data["final_tax"], 2) : 0;
            $amount_with_tax = (isset($amount) && isset($tax_amount)) ? ($amount + $tax_amount) : 0;
            $store = $config_data->store;

            $resource = [
                "amount" => $amount,
                "amount_formatted" => PriceFormat::get($amount, $store->id, "store"),
                "amount_incl_tax" => $amount_with_tax,
                "amount_incl_tax_formatted" => PriceFormat::get($amount_with_tax, $store->id, "store"),
                "tax_amount" => $tax_amount,
                "tax_rate_percent" => $this->total_tax_rate,
                "rules" => collect($this->multiple_rules),
            ];

            if ($callback) {
                $resource = array_merge($resource, $callback());
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

    public function getProductData(int $product_id, object $store): array
    {
        try
        {
            $productBaseRepository = new ProductBaseRepository();
            $product = $productBaseRepository->fetch($product_id);
            $match = [
                "scope" => "store",
                "scope_id" => $store->id,
            ];

            $price = $product->value(array_merge($match, ["attribute_slug" => "price"]));
            $special_price = $product->value(array_merge($match, ["attribute_slug" => "special_price"]));
            $special_from_date = $product->value(array_merge($match, ["attribute_slug" => "special_from_date"]));
            $special_to_date = $product->value(array_merge($match, ["attribute_slug" => "special_to_date"]));
            $tax_class = $product->value(array_merge($match, ["attribute_slug" => "tax_class_id"]));

            $today = date('Y-m-d');
            $currentDate = date('Y-m-d H:m:s', strtotime($today));

            if (isset($special_price)) {
                if (isset($special_from_date)) {
                    $fromDate = date('Y-m-d H:m:s', strtotime($special_from_date));
                }
                if (isset($special_to_date)) {
                    $toDate = date('Y-m-d H:m:s', strtotime($special_to_date));
                }

                if (!isset($fromDate) && !isset($toDate)) {
                    $special_price = 0;
                } else {
                    $special_price = (($currentDate >= $fromDate) && ($currentDate <= $toDate)) ? $special_price : 0;
                }
            }

            $price_array = [
                "regular" => $price ?? 0,
                "special" => $special_price ?? 0,
                "tax_class" => $tax_class?->id,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $price_array;
    }

    public function filterCustomerTaxGroups(object $query, object $customer_tax_group): object
    {
        try
        {
            $data = $query->whereHas('customer_tax_groups', function (Builder $q) use ($customer_tax_group) {
                $q->where('customer_tax_group_id', $customer_tax_group->id);
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function filterCountries(object $query, string $zip_code): mixed
    {
        try
        {
            $data = $query->filter(function ($tax_rate) use ($zip_code) {
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
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

}
