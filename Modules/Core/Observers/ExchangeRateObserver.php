<?php


namespace Modules\Core\Observers;

use Modules\Core\Entities\ExchangeRate;
use Modules\Core\Facades\Audit;

class ExchangeRateObserver
{
    public function created(ExchangeRate $exchange_rate)
    {
        Audit::log($exchange_rate, __FUNCTION__);
    }

    public function updated(ExchangeRate $exchange_rate)
    {
        Audit::log($exchange_rate, __FUNCTION__);
    }

    public function deleted(ExchangeRate $exchange_rate)
    {
        Audit::log($exchange_rate, __FUNCTION__);
    }
}
