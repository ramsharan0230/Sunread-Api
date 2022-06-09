<?php


namespace Modules\Core\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Entities\Configuration;
use Modules\Core\Facades\Audit;

class ConfigurationObserver
{
    public function created(Configuration $configuration)
    {
        Audit::log($configuration, __FUNCTION__);
        Cache::forget("configurations.all");
    }

    public function updated(Configuration $configuration)
    {
        Audit::log($configuration, __FUNCTION__);
        Cache::forget("configurations.all");
    }

    public function deleted(Configuration $configuration)
    {
        Audit::log($configuration, __FUNCTION__);
        Cache::forget("configurations.all");
    }
}
