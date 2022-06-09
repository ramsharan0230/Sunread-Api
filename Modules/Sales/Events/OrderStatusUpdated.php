<?php

namespace Modules\Sales\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated
{
    use Dispatchable;
    use SerializesModels;
    use InteractsWithSockets;

    public object $order;

    public function __construct(object $order)
    {
        $this->order = $order;
    }

    public function broadcastOn(): array
    {
        return [];
    }
}
