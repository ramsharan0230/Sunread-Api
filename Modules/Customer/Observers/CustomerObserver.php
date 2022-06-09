<?php


namespace Modules\Customer\Observers;

use Modules\Core\Facades\Audit;
use Modules\Customer\Entities\Customer;

class CustomerObserver
{
    public function created(Customer $customer)
    {
        Audit::log($customer, __FUNCTION__);
    }

    public function updated(Customer $customer)
    {

        Audit::log($customer, __FUNCTION__);
    }

    public function deleted(Customer $customer)
    {
        Audit::log($customer, __FUNCTION__);
    }
}
