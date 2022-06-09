<?php

namespace Modules\Sales\Listeners;

use Modules\Sales\Events\OrderUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderUpdatedListener
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
     * @param OrderUpdated $event
     * @return void
     */
    public function handle(OrderUpdated $event)
    {
        //
    }
}
