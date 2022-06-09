<?php


namespace Modules\Core\Observers;

use Modules\Core\Entities\Website;
use Modules\Core\Facades\Audit;

class WebsiteObserver
{
    public function created(Website $website)
    {
        Audit::log($website, __FUNCTION__);
    }

    public function updated(Website $website)
    {
        Audit::log($website, __FUNCTION__);
    }

    public function deleted(Website $website)
    {
        Audit::log($website, __FUNCTION__);
    }
}
