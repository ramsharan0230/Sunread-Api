<?php

namespace Modules\Sales\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Sales\Entities\Order;

class SaleOrderListener
{
    public function __construct()
    {
        # code...
    }

    public function createOrderLog(Order $order): void
    {
        dd($order);
    }

    public function updateOrderLog(Order $order): void
    {
        # code...
    }
}
