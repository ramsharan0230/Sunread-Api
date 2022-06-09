<?php

namespace Modules\User\Exceptions;

class AdminAlreadyActiveException extends \Exception
{
    public function __construct()
    {
        parent::__construct(__("core::app.users.users.already-active"));
    }
}
