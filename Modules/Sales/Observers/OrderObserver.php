<?php

namespace Modules\Sales\Observers;

use Modules\Core\Facades\Audit;
use Modules\Erp\Jobs\External\ErpSalesOrderJob;
use Modules\Sales\Entities\Order;

class OrderObserver
{
    public function created(Order $order)
    {
        // Audit::log($order, __FUNCTION__);
    }

    public function updated(Order $order)
    {
        if ($order->isDirty("status")
            && $order->status == "processing"
            && !$order->external_erp_id
        ) {
            ErpSalesOrderJob::dispatch($order)->onQueue("erp");
        }
        // Audit::log($order, __FUNCTION__);
    }

    public function deleted(Order $order)
    {
        // Audit::log($order, __FUNCTION__);
    }
}
