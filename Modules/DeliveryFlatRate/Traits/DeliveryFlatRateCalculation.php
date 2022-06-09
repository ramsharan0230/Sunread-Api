<?php

namespace Modules\DeliveryFlatRate\Traits;

use Exception;
use Modules\Core\Facades\SiteConfig;
use Modules\Sales\Entities\OrderTaxItem;

trait DeliveryFlatRateCalculation
{
    public $orderTaxItemRepostory;
    public $orderTaxRepository;
    public $orderRepository;
    public $orderMetaRepository;

    public function getDeliveryFlatRateCalculate(object $coreCache, object $order): array
    {
        try
        {
            $channel_id = $coreCache?->channel->id;
            $shipping_detail = $this->getShippingCalculation($coreCache, $order);

            $this->orderRepository->update([
                "shipping_method" => $this->method_key,
                "shipping_method_label" => SiteConfig::fetch("delivery_methods_{$this->method_key}_title", "channel", $channel_id)
            ], $order->id, function ($order) use ($shipping_detail) {
                $this->orderMetaRepository->create([
                    "order_id" => $order->id,
                    "meta_key" => "shipping",
                    "meta_value" => [
                        "shipping_method" => $order->shipping_method,
                        "shipping_method_label" => $order->shipping_method_label,
                        "taxes" => $shipping_detail,
                    ]
                ]);
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $shipping_detail;
    }

    public function getShippingCalculation(object $coreCache, object $order): array
    {
        try
        {
            $channel_id = $coreCache?->channel->id;
            $shipping_detail = [
                "shipping_amount" => 0.00,
                "shipping_tax" => false,
            ];
            $flat_type = SiteConfig::fetch("delivery_methods_{$this->method_key}_flat_type", "channel", $channel_id);
            $flat_price = SiteConfig::fetch("delivery_methods_{$this->method_key}_flat_price", "channel", $channel_id);
            $total_shipping_amount = 0.00;
            if ($flat_type !== "per_order") {
                $order->order_taxes->map(function ($order_tax) use (&$total_shipping_amount) {
                    $order_item_total_amount = $order_tax->order_tax_items
                    ->filter(fn ($order_tax_item) => ($order_tax_item->tax_item_type == "product"))
                    ->map(function ($order_item) use ($order_tax, &$total_shipping_amount) {
                        $amount = (float) (($order_tax->percent/100) * $order_item->amount);
                        $total_shipping_amount += $amount;
                        $data = [
                            "tax_id" => $order_tax->id,
                            "tax_percent" => (float) $order_tax->percent,
                            "amount" => $amount,
                            "tax_item_type" => "shipping",
                        ];
                        $match = $data;
                        unset($match["tax_percent"], $match["amount"]);
                        $this->orderTaxItemRepostory->createOrUpdate($match, [
                            "tax_id" => $order_tax->id,
                            "tax_percent" => (float) $order_tax->percent,
                            "amount" => $amount,
                            "tax_item_type" => "shipping",
                        ]);

                        return ($order_item->amount + $amount);
                    })->toArray();

                    $this->orderTaxRepository->update([
                        "amount" => array_sum($order_item_total_amount),
                    ], $order_tax->id);
                });

                $shipping_detail["shipping_tax"] = true;
            } else {
                $total_shipping_amount = $flat_price;
            }

            $shipping_detail["shipping_amount"] = (float) $total_shipping_amount;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $shipping_detail;
    }
}