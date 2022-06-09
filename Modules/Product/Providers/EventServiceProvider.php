<?php

namespace Modules\Product\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Event::listen('catalog.products.create.after', 'Modules\Product\Listeners\ProductListener@indexing');
        Event::listen('catalog.products.update.after', 'Modules\Product\Listeners\ProductListener@indexing');
        Event::listen('catalog.products.delete.after', 'Modules\Product\Listeners\ProductListener@remove');
    }
}
