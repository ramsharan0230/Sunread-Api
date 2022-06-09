<?php

namespace  Modules\PaymentKlarna\Exceptions;

use Exception;

class PaymentKlarnaCheckoutIncompleteException extends Exception
{

    public function __construct()
    {
        parent::__construct(__("core::app.exception_message.payment-klarna-checkout-incomplete"));
    }

}
