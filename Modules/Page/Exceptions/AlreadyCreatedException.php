<?php

namespace Modules\Page\Exceptions;

class AlreadyCreatedException extends \Exception
{
    public function __construct()
    {
        parent::__construct(__("core::app.response.already-created"));
    }
}
