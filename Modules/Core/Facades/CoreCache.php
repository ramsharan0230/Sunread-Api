<?php

namespace Modules\Core\Facades;
use Illuminate\Support\Facades\Facade;

class CoreCache extends Facade
{
    protected static function getFacadeAccessor() {
        return 'coreCache';
    }
}
