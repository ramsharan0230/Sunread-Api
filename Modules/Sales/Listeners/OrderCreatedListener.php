<?php

namespace Modules\Sales\Listeners;

use Modules\Notification\Jobs\SendNotificationJob;
use Modules\Sales\Events\OrderCreated;

class OrderCreatedListener
{
    public function __construct()
    {
    }

    public function handle(OrderCreated $event): void
    {
        SendNotificationJob::dispatch($event->order, "new_order")->onQueue("high");
    }
}
