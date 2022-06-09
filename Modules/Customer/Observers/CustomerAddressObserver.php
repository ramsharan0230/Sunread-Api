<?php


namespace Modules\Customer\Observers;

use Modules\Core\Facades\Audit;
use Modules\Customer\Entities\CustomerAddress;

class CustomerAddressObserver
{
    public function created(CustomerAddress $customer_address)
    {
        Audit::log($customer_address, __FUNCTION__);
    }

    public function updated(CustomerAddress $customer_address)
    {
        Audit::log($customer_address, __FUNCTION__);
    }

    public function deleted(CustomerAddress $customer_address)
    {
        Audit::log($customer_address, __FUNCTION__);
    }
}
