<?php


namespace Modules\Core\Observers;

use Modules\Core\Entities\Currency;
use Modules\Core\Facades\Audit;

class CurrencyObserver
{
    public function created(Currency $currency)
    {
        Audit::log($currency, __FUNCTION__);
    }

    public function updated(Currency $currency)
    {
        Audit::log($currency, __FUNCTION__);
    }

    public function deleted(Currency $currency)
    {
        Audit::log($currency, __FUNCTION__);
    }
}
