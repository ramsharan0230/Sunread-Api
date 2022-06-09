<?php


namespace Modules\ClubHouse\Observers;

use Modules\Core\Facades\Audit;
use Modules\ClubHouse\Entities\ClubHouse;

class ClubHouseObserver
{
    public function created(ClubHouse $clubhouse)
    {
        Audit::log($clubhouse, __FUNCTION__);
        //UrlRewrite::handleUrlRewrite($clubhouse, __FUNCTION__);
    }

    public function updated(ClubHouse $clubhouse)
    {
        Audit::log($clubhouse, __FUNCTION__);
        //UrlRewrite::handleUrlRewrite($clubhouse, __FUNCTION__);
    }

    public function deleted(ClubHouse $clubhouse)
    {
        Audit::log($clubhouse, __FUNCTION__);
        //UrlRewrite::handleUrlRewrite($clubhouse, __FUNCTION__);
    }
}
