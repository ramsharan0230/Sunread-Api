<?php

namespace Modules\Sales\Observers;

use Modules\Core\Facades\Audit;
use Modules\Sales\Entities\OrderItem;

class OrderItemObserver
{
    public function created(OrderItem $orderItem)
    {
        Audit::log($orderItem, __FUNCTION__);
    }

    public function updated(OrderItem $orderItem)
    {
        Audit::log($orderItem, __FUNCTION__);
    }

    public function deleted(OrderItem $orderItem)
    {
        Audit::log($orderItem, __FUNCTION__);
    }
}
