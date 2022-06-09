<?php

namespace Modules\PaymentAdyen\Exceptions;

class OrderNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct(__("core::app.response.not-found", ["name" => "Order"]));
    }
}
