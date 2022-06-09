<?php

namespace Modules\Sales\Providers;

use Illuminate\Support\Facades\Event;
use Modules\Sales\Events\OrderCreated;
use Modules\Sales\Listeners\OrderListener;
use Modules\Sales\Events\OrderStatusUpdated;
use Modules\Sales\Events\OrderCommentCreated;
use Modules\Sales\Listeners\OrderCreatedListener;
use Modules\Sales\Listeners\OrderStatusUpdatedListener;
use Modules\Sales\Listeners\OrderCommentCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderCreated::class => [
            OrderCreatedListener::class,
        ],
        OrderCommentCreated::class => [
            OrderCommentCreatedListener::class,
        ],
        OrderStatusUpdated::class => [
            OrderStatusUpdatedListener::class,
        ],
    ];

    public function boot(): void
    {
        Event::listen("order.create.update.after", [OrderListener::class, "orderLog"]);
        Event::listen("order.transaction.create.update.after", [OrderListener::class, "transactionLog"]);
        Event::listen("order.comment.create.after", [OrderListener::class, "createOrderComment"]);
    }
}
