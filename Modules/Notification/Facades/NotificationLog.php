<?php

namespace Modules\Notification\Facades;
use Illuminate\Support\Facades\Facade;

class NotificationLog extends Facade
{
    protected static function getFacadeAccessor() {
        return 'notificationLog';
    }
}
