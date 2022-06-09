<?php

namespace Modules\Tax\Facades;
use Illuminate\Support\Facades\Facade;

class NewTaxPrices extends Facade
{
    protected static function getFacadeAccessor(): string 
    {
        return 'NewTaxPrices';
    }
}