<?php

namespace Modules\Tax\Facades;
use Illuminate\Support\Facades\Facade;

class GlobalCalculation extends Facade
{
    protected static function getFacadeAccessor() {
        return 'GlobalCalculation';
    }
}