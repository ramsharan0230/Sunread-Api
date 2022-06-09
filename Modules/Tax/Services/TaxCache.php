<?php

namespace Modules\Tax\Services;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Modules\Country\Entities\Country;
use Modules\Tax\Entities\CustomerTaxGroup;
use Modules\Tax\Entities\ProductTaxGroup;
use Modules\Tax\Entities\TaxRate;
use Modules\Tax\Entities\TaxRule;

class TaxCache 
{
    protected $cache_days = 3;

    public function setProductTaxGroup(): void
    {
        try
        {
            Cache::forget("product_tax_group");
            Cache::remember("product_tax_group", Carbon::now()->addDays($this->cache_days), function () {
                return ProductTaxGroup::with(["tax_rules.tax_rates.country"])->get();
            });
            $this->taxAndProductCache();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function setCustomerTaxGroup(): void
    {
        try
        {
            Cache::forget("customer_tax_group");
            Cache::remember("customer_tax_group", Carbon::now()->addDays($this->cache_days), function () {
                return CustomerTaxGroup::with(["tax_rules.tax_rates.country"])->get();
            });
            
            $this->taxAndProductCache();
        }
        catch (Exception $exception)
        {
            throw $exception;
        } 
    }

    public function setTaxRule(): void
    {
        try
        {
            Cache::forget("tax_rule");
            Cache::remember("tax_rule", Carbon::now()->addDays($this->cache_days), function () {
                return TaxRule::with(["tax_rates.country", "product_tax_groups", "customer_tax_groups"])->get();
            });   
            $this->taxAndProductCache();
        }
        catch (Exception $exception)
        {
            throw $exception;
        } 
    }

    public function setTaxRate(): void
    {
        try
        {
            Cache::forget("tax_rate");
            Cache::remember("tax_rate", Carbon::now()->addDays($this->cache_days), function () {
                return TaxRate::with(["country", "region", "tax_rules"])->get();
            });  
            $this->taxAndProductCache();
        }
        catch (Exception $exception)
        {
            throw $exception;
        } 
    }

    public function setCountry(): void
    {
        try
        {
            Cache::forget("country");
            Cache::remember("country", Carbon::now()->addDays($this->cache_days), function () {
                return Country::with(["region"])->get();
            });  
            $this->taxAndProductCache();
        }
        catch (Exception $exception)
        {
            throw $exception;
        } 
    }
    
    public function productTaxGroup(): mixed
    {
        try
        {
            if (!Cache::has("product_tax_group")) {
                Cache::remember("product_tax_group", Carbon::now()->addDays($this->cache_days), function () {
                    return ProductTaxGroup::with(["tax_rules.tax_rates.country"])->get();
                });
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return Cache::get("product_tax_group");
    }

    public function customerTaxGroup(): mixed
    {
        try
        {
            if (!Cache::has("customer_tax_group")) {
                Cache::remember("customer_tax_group", Carbon::now()->addDays($this->cache_days), function () {
                    return CustomerTaxGroup::with(["tax_rules.tax_rates.country"])->get();
                });
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return Cache::get("customer_tax_group");
    }

    public function taxRate(): mixed
    {
        try
        {
            if (!Cache::has("tax_rate")) {
                Cache::remember("tax_rate", Carbon::now()->addDays($this->cache_days), function () {
                    return TaxRate::with(["country", "region", "tax_rules"])->get();
                });
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return Cache::get("tax_rate");
    }

    public function taxRule(): mixed
    {
        try
        {
            if (!Cache::has("tax_rule")) {
                Cache::remember("tax_rule", Carbon::now()->addDays($this->cache_days), function () {
                    return TaxRule::with(["tax_rates.country", "product_tax_groups", "customer_tax_groups"])->get();
                });
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return Cache::get("tax_rule");
    }

    public function country(): mixed
    {
        try
        {
            if (!Cache::has("country")) {
                Cache::remember("country", Carbon::now()->addDays($this->cache_days), function () {
                    return Country::with(["regions"])->get();
                });
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return Cache::get("country");
    }

    public function taxAndProductCache(): void
    {
        try
        {
            $cache_names = [ "taxCache-product", "product_details" ];
            foreach ($cache_names as $cache_name) {
                $cache_key = Redis::keys("{$cache_name}*");
                if ( count($cache_key) > 0 ) {
                    Redis::del($cache_key);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

}
