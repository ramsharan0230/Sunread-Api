<?php

namespace Modules\PaymentAdyen\Exceptions;

class InvalidAdyenNotificationRequest extends \Exception
{
    public function __construct()
    {
        parent::__construct(__("core::app.response.invalid-adyen-notification-request"));
    }
}
