<?php

namespace Modules\PaymentKlarna\Traits;

use Exception;
use Illuminate\Support\Str;
use Modules\Core\Facades\SiteConfig;
use Modules\Sales\Entities\OrderTax;
use Modules\Country\Entities\Country;
use Modules\Sales\Entities\OrderItem;
use Modules\Sales\Entities\OrderAddress;
use Modules\Sales\Entities\OrderTaxItem;
use Modules\Sales\Facades\OrderStatusHelper;
use Modules\Customer\Entities\CustomerAddress;

trait HasKlarnaOrderMigrator
{
    private function updateFinalOrderCalculations(object $order): void
    {
        try
        {
            $grand_total = (float) $order->grand_total + $this->getShippingAmount() - $this->getKlarnaDiscount() + $this->getKlarnaSurCharge();
            $order_addresses = $order->order_addresses()->get();
            $order->update([
                "grand_total" => $grand_total,
                "shipping_amount" => $this->getShippingAmount(),
                "billing_address_id" => $order_addresses->where("address_type", "billing")->first()->id,
                "shipping_address_id" => $order_addresses->where("address_type", "shipping")->first()->id,
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    private function orderData(): array
    {
        try
        {
            $status = OrderStatusHelper::getStateLatestOrderStatus("pending")->slug;
            $data = array_merge([
                "shipping_method_label" => $this->klarna_response->selected_shipping_option["name"],
                "shipping_method" => Str::slug($this->klarna_response->selected_shipping_option["name"]),
                "payment_method_label" => SiteConfig::fetch("payment_methods_klarna_title", "channel", $this->klarna_order->channel_id),
                "status" => $status,
            ], $this->getKlarnaCustomer());
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getKlarnaCustomer(): array
    {
        try
        {
            $klarna_customer = $this->klarna_response->billing_address;
            $data = [
                "customer_email" => $klarna_customer['email'],
                "customer_first_name" => $klarna_customer['given_name'],
                "customer_middle_name" => "",
                "customer_last_name" => $klarna_customer['family_name'],
                "customer_phone" => $klarna_customer['phone'],
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    private function createOrderItem(object $order): void
    {
        try
        {
            $this->klarna_order->order_items()->get()->map(function ($klarna_order) use ($order) {

                $klarna_order = $klarna_order->toArray();
                unset($klarna_order["id"], $klarna_order["order_id"]);
                $klarna_order = array_merge([
                    "order_id" => $order->id,
                    "product_options" => json_encode($klarna_order["product_options"]),
                ], $klarna_order);

                OrderItem::updateOrCreate([
                    "order_id" => $order->id,
                    "product_id" => $klarna_order["product_id"],
                ], $klarna_order);
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    private function createOrderAddress(object $order): void
    {
        try
        {
            $addresses = ["shipping" => $this->klarna_response->shipping_address, "billing" => $this->klarna_response->billing_address];
            foreach ($addresses as $type => $order_address) {
                $orderAddressData = [
                    "customer_id" => $order->customer_id,
                    "first_name" => $order_address['given_name'],
                    "last_name" => $order_address['family_name'],
                    "phone" => array_key_exists("phone", $order_address) ?  $order_address['phone'] : " ",
                    "address1" => array_key_exists("street_address", $order_address) ? $order_address['street_address'] : "",
                    "address2" => array_key_exists("street_address2", $order_address) ? $order_address['street_address2'] : null,
                    "postcode" => $order_address['postal_code'],
                    "country_id" => Country::where("iso_2_code", $order_address['country'])->first()->id,
                    "region_name" => $order_address["region"] ?? null,
                    "city_name" => $order_address["city"] ?? null,
                ];
                if ($order->customer_id && $type == "billing") {
                    $data = ["default_billing_address" => 1];
                    $orderAddressData["default_billing_address"] = 1;
                } elseif ($order->customer_id && $type == "shipping") {
                    $data = ["default_shipping_address" => 1];
                    $orderAddressData["default_shipping_address"] = 1;
                }

                if ($order->customer_id) {
                    $data['customer_id'] = $order->customer_id;
                    $customer_address = CustomerAddress::updateOrCreate($data, $orderAddressData);
                }
                $orderAddressData["order_id"] = $order->id;
                $orderAddressData["customer_address_id"] = isset($customer_address) ? $customer_address->id : null;
                $orderAddressData["address_type"] = $type;
                $orderAddressData["email"] = $order_address['email'];
                OrderAddress::updateOrCreate([
                    "order_id" => $order->id,
                    "address_type" => $type,
                ], $orderAddressData);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    private function createOrderTaxes(object $order): void
    {
        try
        {
            $this->klarna_order->order_taxes()->get()->map(function ($klarna_order_tax) use ($order) {
                $data = $klarna_order_tax->toArray();
                unset($data["order_id"], $data["id"]);
                $data = array_merge([
                    "order_id" => $order->id,
                ], $data);

                $order_tax = OrderTax::updateOrCreate([
                    "order_id" => $order->id,
                    "code" => $klarna_order_tax->code,
                ], $data);

                $klarna_order_tax->order_tax_items()->get()->map(function ($order_tax_item) use ($order_tax) {
                    OrderTaxItem::updateOrCreate([
                        "tax_id" => $order_tax->id,
                        "item_id" => $order_tax_item->item_id,
                        "tax_item_type" => "product"
                    ], [
                        "tax_id" => $order_tax->id,
                        "item_id" => $order_tax_item->item_id,
                        "tax_percent" => $order_tax_item->tax_percent,
                        "amount" => $order_tax_item->amount,
                        "tax_item_type" => "product",
                    ]);
                });
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    private function getShippingAmount(): float
    {
        try
        {
            $shipping_amount = 0.00;
            foreach($this->klarna_response->order_lines as $item) {
                if ($item["type"] == "shipping_fee") {
                    $shipping_amount = (float) $item["total_amount"]/100;
                    break;
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $shipping_amount;
    }

    private function getKlarnaDiscount(): float    // TODO:: ASK Which field to select for Discount Amount when type is discount
    {
        // foreach($this->klarna_response->order_lines as $item) {
        //     return ($item["type"] == "discount") ? (float) $item["total_amount"]/100 : 0.00;
        // }

        return 0.00;
    }

    private function getKlarnaSurCharge(): float    // TODO:: ASK Which field to select for Discount Amount when type is surcharge
    {
        // foreach($this->klarna_response->order_lines as $item) {
        //     return ($item["type"] == "surcharge") ? (float) $item["total_amount"]/100 : 0.00;
        // }

        return 0.00;
    }

    private function updateKlarnaOrder(object $order): void
    {
        try
        {
            $this->klarna_order->update([
                "order_id" => $order->id,
                "shipping_amount" => $order->shipping_amount,
                "discount_amount" => $order->discount_amount,
                "grand_total" => $order->grand_total,
                "sub_total" => $order->sub_total,
                "tax_amount" =>$order->tax_amount,
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }
}
