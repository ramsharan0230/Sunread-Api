<?php

namespace Modules\Tax\Facades;

use Illuminate\Support\Facades\Facade;

class TaxCache extends Facade
{
    protected static function getFacadeAccessor() {
        return 'TaxCache';
    }
}