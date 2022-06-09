<?php

namespace Modules\Sales\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class NotAllowedException extends Exception
{
    public function __construct()
    {
        parent::__construct(__("core::app.response.status-change-failed"), Response::HTTP_FORBIDDEN);
    }
}
