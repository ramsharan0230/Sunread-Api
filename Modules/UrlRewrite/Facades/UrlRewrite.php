<?php

namespace Modules\UrlRewrite\Facades;
use Illuminate\Support\Facades\Facade;
use Modules\UrlRewrite\Contracts\UrlRewriteInterface;

class UrlRewrite extends Facade
{
    protected static function getFacadeAccessor(): string 
	{
        return UrlRewriteInterface::class;
    }
}
