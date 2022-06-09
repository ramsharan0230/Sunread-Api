<?php

namespace Modules\Inventory\Exceptions;

class InventoryCannotBeLessThanZero extends \Exception
{
    public function __construct()
    {
        parent::__construct(__("core::app.response.inventory_cannot_be_zero"));
    }
}
