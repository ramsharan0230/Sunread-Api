<?php

namespace Modules\Tax\Facades;
use Illuminate\Support\Facades\Facade;

class TaxPrice extends Facade
{
    protected static function getFacadeAccessor() {
        return 'TaxPrice';
    }
}