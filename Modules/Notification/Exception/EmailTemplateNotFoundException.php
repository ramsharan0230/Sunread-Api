<?php

namespace  Modules\Notification\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class EmailTemplateNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct(__("core::app.response.not-found", [ "name" => "Email Template"]), Response::HTTP_NOT_FOUND);
    }
}