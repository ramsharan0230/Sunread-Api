<?php

namespace Modules\UrlRewrite\Observers;

use Modules\Core\Facades\Audit;
use Modules\UrlRewrite\Entities\UrlRewrite;

class UrlRewriteObserver
{
	public function created(UrlRewrite $UrlRewrite)
    {
        Audit::log($UrlRewrite, __FUNCTION__);
    }

    public function updated(UrlRewrite $UrlRewrite)
    {
        Audit::log($UrlRewrite, __FUNCTION__);
    }

    public function deleted(UrlRewrite $UrlRewrite)
    {
        Audit::log($UrlRewrite, __FUNCTION__);
    }
}