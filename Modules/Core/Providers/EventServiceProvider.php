<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Event::listen('core.stores.create.after', 'Modules\Core\Listeners\StoreListener@indexing');
        // Event::listen('core.stores.update.after', 'Modules\Core\Listeners\StoreListener@indexing');
//
        Event::listen('core.website.create.after', 'Modules\Core\Listeners\WebsiteListener@create');
        Event::listen('core.website.update.before', 'Modules\Core\Listeners\WebsiteListener@beforeUpdate');
        Event::listen('core.website.update.after', 'Modules\Core\Listeners\WebsiteListener@update');
        Event::listen('core.website.delete.before', 'Modules\Core\Listeners\WebsiteListener@beforeDelete');
        Event::listen('core.website.delete.after', 'Modules\Core\Listeners\WebsiteListener@delete');

        Event::listen('core.channel.create.after', 'Modules\Core\Listeners\ChannelListener@create');
        Event::listen('core.channel.update.before', 'Modules\Core\Listeners\ChannelListener@beforeUpdate');
        Event::listen('core.channel.update.after', 'Modules\Core\Listeners\ChannelListener@update');
        Event::listen('core.channel.delete.before', 'Modules\Core\Listeners\ChannelListener@beforeDelete');
        Event::listen('core.channel.delete.after', 'Modules\Core\Listeners\ChannelListener@delete');

        Event::listen('core.stores.create.after', 'Modules\Core\Listeners\StoreListener@create');
        Event::listen('core.stores.update.before', 'Modules\Core\Listeners\StoreListener@beforeUpdate');
        Event::listen('core.stores.update.after', 'Modules\Core\Listeners\StoreListener@update');
        Event::listen('core.stores.delete.after', 'Modules\Core\Listeners\StoreListener@delete');
    }
}
