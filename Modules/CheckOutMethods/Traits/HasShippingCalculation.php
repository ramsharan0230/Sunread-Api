<?php

namespace Modules\CheckOutMethods\Traits;

use Exception;

trait HasShippingCalculation
{
    public function updateOrderCalculation(
        array $arr_shipping_amount,
        object $order,
        float $sub_total,
        float $discount_amount,
        float $sub_total_tax_amount,
        float $total_qty_ordered,
        int $total_items
    ): void {
        try
        {
            $cal_shipping_amt = (float) ($arr_shipping_amount["shipping_tax"] ? 0.00 : $arr_shipping_amount["shipping_amount"]);
            $taxes = $order->order_taxes?->pluck("amount")->toArray();
            $total_tax = array_sum($taxes);
            $grand_total = ($sub_total + $cal_shipping_amt + $total_tax - $discount_amount);
            $total_tax_without_shipping = $total_tax - ($arr_shipping_amount["shipping_tax"] ? $arr_shipping_amount["shipping_amount"] : 0.00);

            $order_addresses = $order->order_addresses()->get();
            $order->update([
                "sub_total" => $sub_total,
                "sub_total_tax_amount" => $sub_total_tax_amount,
                "tax_amount" => $total_tax_without_shipping,
                "shipping_amount" => $arr_shipping_amount["shipping_amount"],
                "grand_total" => $grand_total,
                "total_items_ordered" => $total_items,
                "total_qty_ordered" => $total_qty_ordered,
                "billing_address_id" => $order_addresses->where("address_type", "billing")->first()?->id,
                "shipping_address_id" => $order_addresses->where("address_type", "shipping")->first()?->id,
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

}
