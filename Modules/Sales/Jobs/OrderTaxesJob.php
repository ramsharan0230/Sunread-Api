<?php

namespace Modules\Sales\Jobs;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Modules\Sales\Entities\OrderTax;
use Modules\Sales\Entities\OrderTaxItem;
use Modules\Sales\Traits\HasOrderProductDetail;

class OrderTaxesJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasOrderProductDetail;

    protected $order_item_details, $order;

    public function __construct(object $order, object $order_item_details)
    {
        $this->order_item_details = $order_item_details;
        $this->order = $order;
    }

    public function handle(): void
    {
        try
        {
            $this->storeOrderTax(function ($order_tax, $order_item_details, $rule) {
                $this->storeOrderTaxItem($order_tax, $order_item_details, $rule);
            });
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    private function storeOrderTax(?callable $callback = null): void
    {
        DB::beginTransaction();
        try
        {
            foreach ($this->order_item_details->rules as $rule)
            {
                $data = [
                    "order_id" => $this->order->id,
                    "code" => \Str::slug($rule->name),
                    "title" => $rule->name,
                    "percent" => $this->order_item_details->tax_rate_percent,
                    "amount" => 0
                ];
                $match = $data;
                unset($match["title"], $match["percent"], $match["amount"]);
                $order_tax = OrderTax::updateOrCreate($match, $data);
                if ($callback) $callback($order_tax, $this->order_item_details, $rule);
            }
        }
        catch ( Exception $exception )
        {
            DB::rollback();
            throw $exception;
        }

        DB::commit();
    }

    private function storeOrderTaxItem(object $order_tax, object $order_item_details, mixed $rule): object
    {
        DB::beginTransaction();
        try
        {
            $data = [
                "tax_id" => $order_tax->id,
                "item_id" => $order_item_details->product_id,
                "tax_percent" => $order_tax->percent,
                "amount" => ($rule->rates?->pluck("tax_rate_value")->first() * $order_item_details->qty),
                "tax_item_type" => "product"
            ];            
            $order_tax_item = OrderTaxItem::create($data);  
        }
        catch ( Exception $exception )
        {
            DB::rollback();
            throw $exception;
        }

        DB::commit(); 
        return $order_tax_item;
    }
}
