<?php

namespace Modules\Erp\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ErpTokenInvalidException extends Exception
{
    public function __construct()
    {
        parent::__construct(__("core::app.response.invalid-token"), Response::HTTP_FORBIDDEN);
    }
}