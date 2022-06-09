<?php

namespace Modules\Notification\Listeners;

use Modules\Notification\Events\ResetPassword;
use Modules\Notification\Jobs\SendNotificationJob;

class SendPasswordChangeSuccess
{
    public function __construct()
    {
    }

    public function handle(ResetPassword $event): void
    {
        SendNotificationJob::dispatch($event->user, "reset_password")->onQueue("high");
    }
}
