<?php

namespace Modules\CheckOutMethods\Exceptions;

class MethodException extends \Exception
{
    public function __construct(?string $message = "", ?int $status = 500)
    {
        parent::__construct($message, $status);
    }
}