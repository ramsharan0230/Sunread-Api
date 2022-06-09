<?php

namespace Modules\Customer\Listeners;

use Modules\Customer\Events\GuestRegistration;
use Modules\Notification\Jobs\SendNotificationJob;

class SendGuestRegistration
{
    public function __construct()
    {
    }
    
    public function handle(GuestRegistration $event): void
    {
        SendNotificationJob::dispatch($event->user, "guest_registration", $event->password)->onQueue("high");
    }
}
