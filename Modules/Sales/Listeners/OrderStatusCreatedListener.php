<?php

namespace Modules\Sales\Listeners;

use Modules\Sales\Events\OrderStatusCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderStatusCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param OrderStatusCreated $event
     * @return void
     */
    public function handle(OrderStatusCreated $event)
    {
        //
    }
}
