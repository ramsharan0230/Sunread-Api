<?php

namespace Modules\Notification\Listeners;

use Modules\Notification\Events\ConfirmEmail;
use Modules\Notification\Jobs\SendNotificationJob;

class SendAccountConfirmation
{
    public function __construct()
    {
    }

    public function handle(ConfirmEmail $event): void
    {
        SendNotificationJob::dispatch($event->user, "confirmed_email")->onQueue("high");
    }
}
