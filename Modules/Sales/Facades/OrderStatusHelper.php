<?php

namespace Modules\Sales\Facades;
use Illuminate\Support\Facades\Facade;

class OrderStatusHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'OrderStatusHelper';
    }
}