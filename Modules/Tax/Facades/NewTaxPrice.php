<?php

namespace Modules\Tax\Facades;
use Illuminate\Support\Facades\Facade;

/**
 * 
 * @method static object calculate( object $request,mixed $price, ?int $product_tax_group_id = null, ?int $customer_tax_group_id = 1, ?int $country_id = null, ?string $zip_code = null, ?callable $callback = null) 
 */
class NewTaxPrice extends Facade
{
    protected static function getFacadeAccessor() {
        return 'NewTaxPrice';
    }
}