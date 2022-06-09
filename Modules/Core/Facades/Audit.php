<?php

namespace Modules\Core\Facades;
use Illuminate\Support\Facades\Facade;

class Audit extends Facade
{
    protected static function getFacadeAccessor() {
        return 'audit';
    }
}
