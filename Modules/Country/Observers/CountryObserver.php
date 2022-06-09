<?php

namespace Modules\Country\Observers;

use Modules\Core\Facades\Audit;
use Modules\Country\Entities\Country;
use Modules\Tax\Facades\TaxCache;

class CountryObserver
{
    public function created(Country $Country)
    {
        Audit::log($Country, __FUNCTION__);
        TaxCache::setCountry();
    }

    public function updated(Country $Country)
    {
        Audit::log($Country, __FUNCTION__);
        TaxCache::setCountry();
    }

    public function deleted(Country $Country)
    {
        Audit::log($Country, __FUNCTION__);
        TaxCache::setCountry();
    }
}
