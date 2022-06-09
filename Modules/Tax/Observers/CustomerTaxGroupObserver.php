<?php

namespace Modules\Tax\Observers;

use Modules\Core\Facades\Audit;
use Modules\Tax\Entities\CustomerTaxGroup;
use Modules\Tax\Facades\TaxCache;

class CustomerTaxGroupObserver
{
    public function created(CustomerTaxGroup $productTaxCustomerTaxGroup)
    {
        Audit::log($productTaxCustomerTaxGroup, __FUNCTION__);
        TaxCache::setCustomerTaxGroup();
    }

    public function updated(CustomerTaxGroup $productTaxCustomerTaxGroup)
    {
        Audit::log($productTaxCustomerTaxGroup, __FUNCTION__);
        TaxCache::setCustomerTaxGroup();
    }

    public function deleted(CustomerTaxGroup $productTaxCustomerTaxGroup)
    {
        Audit::log($productTaxCustomerTaxGroup, __FUNCTION__);
        TaxCache::setCustomerTaxGroup();
    }
}
