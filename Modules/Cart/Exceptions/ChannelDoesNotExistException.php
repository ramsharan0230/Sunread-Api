<?php

namespace  Modules\Cart\Exceptions;

class ChannelDoesNotExistException extends \Exception
{
    public function __construct()
    {
        parent::__construct(__("core::app.response.not-found", ["name" => "Channel"]));
    }
}
