<?php

namespace Modules\Sales\Listeners;

use Modules\Notification\Jobs\SendNotificationJob;
use Modules\Sales\Events\OrderCommentCreated;

class OrderCommentCreatedListener
{
    public function __construct()
    {
    }

    public function handle(OrderCommentCreated $event): void
    {
        SendNotificationJob::dispatch($event->comment, "order_comment")->onQueue("high");
    }
}
