<?php

namespace Modules\Customer\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Core\Facades\Audit;

class SessionListener
{
    public function customerLogin($customer)
    {
        $customer->last_login_at = now();
        $customer->save();
        Audit::log($customer, "login", "Customer Login", "{$customer->full_name} logged in.");
    }

    public function customerLogOut($customer)
    {
        Audit::log($customer, "login", "Customer Logout", "{$customer->full_name} logged out.");
    }
}
