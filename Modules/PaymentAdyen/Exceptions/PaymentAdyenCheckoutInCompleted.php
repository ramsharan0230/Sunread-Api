<?php

namespace Modules\PaymentAdyen\Exceptions;

use Exception;

class PaymentAdyenCheckoutInCompleted extends Exception
{
    public function __construct()
    {
        parent::__construct(__("core::app.response.adyen-payment-incomplete"));
    }
}
