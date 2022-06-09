<?php

namespace Modules\Sales\Listeners;

use Modules\Notification\Jobs\SendNotificationJob;
use Modules\Sales\Events\OrderStatusUpdated;

class OrderStatusUpdatedListener
{
    public function __construct()
    {
    }

    public function handle(OrderStatusUpdated $event): void
    {
        SendNotificationJob::dispatch($event->order, "order_status_update")->onQueue("high");
    }
}
