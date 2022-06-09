<?php

namespace Modules\Core\Facades;
use Illuminate\Support\Facades\Facade;

class SiteConfig extends Facade
{
    protected static function getFacadeAccessor() {
        return 'siteConfig';
    }
}
