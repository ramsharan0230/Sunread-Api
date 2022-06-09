<?php


namespace Modules\ClubHouse\Observers;

use Illuminate\Support\Facades\Bus;
use Modules\UrlRewrite\Facades\UrlRewrite;
use Modules\ClubHouse\Entities\ClubHouseValue;
use Modules\Core\Entities\Website;
use Modules\Product\Jobs\SingleIndexing;

class ClubHouseValueObserver
{
    public function created(ClubHouseValue $clubhouse_value)
    {

    }

    public function updated(ClubHouseValue $clubhouse_value)
    {

    }

    public function deleted(ClubHouseValue $clubhouse_value)
    {
    }
}
