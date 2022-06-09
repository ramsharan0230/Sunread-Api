<?php


namespace Modules\Customer\Observers;

use Modules\Core\Facades\Audit;
use Modules\Customer\Entities\CustomerGroup;

class CustomerGroupObserver
{
    public function created(CustomerGroup $customer_group)
    {
        Audit::log($customer_group, __FUNCTION__);
    }

    public function updated(CustomerGroup $customer_group)
    {
        Audit::log($customer_group, __FUNCTION__);
    }

    public function deleted(CustomerGroup $customer_group)
    {
        Audit::log($customer_group, __FUNCTION__);
    }
}
