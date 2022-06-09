<?php

namespace Modules\Notification\Listeners;

use Modules\Notification\Events\ForgotPassword;
use Modules\Notification\Jobs\SendNotificationJob;

class SendPasswordResetLink
{
    public function __construct()
    {
    }

    public function handle(ForgotPassword $event): void
    {
        SendNotificationJob::dispatch($event->user, "forgot_password", $event->token)->onQueue("high");
    }
}
