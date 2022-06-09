<?php

namespace Modules\Notification\Listeners;

use Modules\Notification\Events\NewAccount;
use Modules\Notification\Jobs\SendNotificationJob;

class SendNewAccount
{
    public function __construct()
    {
    }

    public function handle(NewAccount $event): void
    {
        SendNotificationJob::dispatch($event->user, "new_account")->onQueue("high");
    }
}
