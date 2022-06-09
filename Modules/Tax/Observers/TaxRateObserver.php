<?php

namespace Modules\Tax\Observers;

use Modules\Core\Facades\Audit;
use Modules\Tax\Entities\TaxRate;
use Modules\Tax\Facades\TaxCache;

class TaxRateObserver
{
    public function created(TaxRate $TaxRate)
    {
        Audit::log($TaxRate, __FUNCTION__);
        TaxCache::setTaxRate();
    }

    public function updated(TaxRate $TaxRate)
    {
        Audit::log($TaxRate, __FUNCTION__);
        TaxCache::setTaxRate();
    }

    public function deleted(TaxRate $TaxRate)
    {
        Audit::log($TaxRate, __FUNCTION__);
        TaxCache::setTaxRate();
    }
}
