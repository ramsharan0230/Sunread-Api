<?php

namespace Modules\Product\Observers;

use Illuminate\Support\Facades\Redis;
use Modules\Core\Facades\Audit;
use Modules\Product\Entities\Feature;

class FeatureObserver
{
    public function created(Feature $feature)
    {
        Audit::log($feature, __FUNCTION__);
    }

    public function updated(Feature $feature)
    {
        $this->cacheClear();
        Audit::log($feature, __FUNCTION__);
    }

    public function deleted(Feature $feature)
    {
        $this->cacheClear();
        Audit::log($feature, __FUNCTION__);
    }

    public function cacheClear(): void
    {
        $cache_name = "product_details_*";
        $cache_key = Redis::keys($cache_name);
        if (count($cache_key) > 0 ) {
            Redis::del($cache_key);
        }
    }
}
