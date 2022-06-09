<?php

namespace  Modules\Core\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class DestructiveMethodException extends Exception
{
    public function __construct(?string $message = null, ?int $http_code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        parent::__construct($message ?? __("core::app.exception_message.destructive-method-forbidden"), $http_code);
    }
}
