<?php

namespace Modules\User\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen('admin.session.login.after', 'Modules\User\Listeners\SessionListener@adminLogin');
        Event::listen('admin.session.logout.after', 'Modules\User\Listeners\SessionListener@adminLogOut');
    }
}
