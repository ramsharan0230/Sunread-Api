<?php

namespace Modules\User\Listeners;

use Modules\Core\Facades\Audit;

class SessionListener
{
    public function adminLogin($admin)
    {
        Audit::log($admin, "login", "Admin Login", "{$admin->full_name} logged in.");
    }

    public function adminLogOut($admin)
    {
        Audit::log($admin, "login", "Admin Logout", "{$admin->full_name} logged out.");
    }
}
