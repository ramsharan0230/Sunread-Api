<?php

namespace Modules\Core\Services;

class Pipe
{
    public function __construct($value)
    {
        $this->id = "__pipe-" . uniqid();
        $GLOBALS[$this->id] = $value;
    }

    public function __destruct()
    {
        unset($GLOBALS[$this->id]);
    }

    public function pipe($value)
    {
        $GLOBALS[$this->id] = $value;
        return $this;
    }

    public function __get($name)
    {
        if ($name === "value") {
            return $GLOBALS[$this->id];
        }

        trigger_error("Property {$name} doesn't exist and cannot be taken", E_USER_ERROR);
    }
}