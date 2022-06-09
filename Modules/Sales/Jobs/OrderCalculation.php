<?php

namespace Modules\Sales\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Sales\Traits\HasOrderCalculation;

class OrderCalculation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasOrderCalculation;

    protected $order, $request, $coreCache;

    public function __construct(object $order, object $request, object $coreCache)
    {
        $this->order = $order;
        $this->request = $request;
        $this->coreCache = $coreCache;
    }

    public function handle(): void
    {
        try
        {
            $this->order->order_taxes->map( function ($order_tax) {
                $order_tax_item_amount = $order_tax->order_tax_items->map( function ($order_item) {
                    return $order_item->amount;
                })->toArray();
                $order_tax->update(["amount" => array_sum($order_tax_item_amount)]);
            });

            $this->orderCalculationUpdate($this->order, $this->request, $this->coreCache);
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }
    }
}
