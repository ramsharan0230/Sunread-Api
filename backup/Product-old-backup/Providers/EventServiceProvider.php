<?php

namespace Modules\Product\Providers;

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
        Event::listen('catalog.attribute.create.after', 'Modules\Product\Listeners\ProductFlatTable@afterAttributeCreatedUpdated');

        Event::listen('catalog.attribute.update.after', 'Modules\Product\Listeners\ProductFlatTable@afterAttributeCreatedUpdated');

        // Event::listen('catalog.attribute.delete.before', 'Modules\Product\Listeners\ProductFlatTable@afterAttributeDeleted');

        Event::listen('catalog.product.create.after', 'Modules\Product\Listeners\ProductFlatTable@afterProductCreatedUpdated');

        Event::listen('catalog.product.update.after', 'Modules\Product\Listeners\ProductFlatTable@afterProductCreatedUpdated');
    }
}
