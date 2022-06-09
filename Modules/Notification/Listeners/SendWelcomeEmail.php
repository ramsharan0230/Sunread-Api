<?php

namespace Modules\Notification\Listeners;

use Modules\Notification\Events\RegistrationSuccess;
use Modules\Notification\Jobs\SendNotificationJob;

class SendWelcomeEmail
{
    public function __construct()
    {
    }

    public function handle(RegistrationSuccess $event): void
    {
        SendNotificationJob::dispatch($event->user, "welcome_email")->onQueue("high");
    }
}
