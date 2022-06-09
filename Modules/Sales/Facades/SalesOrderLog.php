<?php

namespace Modules\Sales\Facades;
use Illuminate\Support\Facades\Facade;

class SalesOrderLog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'SalesOrderLog';
    }
}