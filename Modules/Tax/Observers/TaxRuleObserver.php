<?php

namespace Modules\Tax\Observers;

use Modules\Core\Facades\Audit;
use Modules\Tax\Entities\TaxRule;
use Modules\Tax\Facades\TaxCache;

class TaxRuleObserver
{
    public function created(TaxRule $TaxRule)
    {
        Audit::log($TaxRule, __FUNCTION__);
        TaxCache::setTaxRule();
    }

    public function updated(TaxRule $TaxRule)
    {
        Audit::log($TaxRule, __FUNCTION__);
        TaxCache::setTaxRule();
    }

    public function deleted(TaxRule $TaxRule)
    {
        Audit::log($TaxRule, __FUNCTION__);
        TaxCache::setTaxRule();
    }
}
