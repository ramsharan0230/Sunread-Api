<?php

namespace  Modules\Cart\Exceptions;

class OutOfStockException extends \Exception
{
    public function __construct()
    {
        parent::__construct(__("core::app.response.not-enough-stock-quantity"));
    }
}
