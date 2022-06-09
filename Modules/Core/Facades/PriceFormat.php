<?php

namespace Modules\Core\Facades;
use Illuminate\Support\Facades\Facade;

class PriceFormat extends Facade
{
    protected static function getFacadeAccessor() {
        return 'priceFormat';
    }
}