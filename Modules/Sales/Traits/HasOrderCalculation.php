<?php

namespace Modules\Sales\Traits;

use Exception;
use Illuminate\Support\Str;
use Modules\Sales\Entities\Order;
use Illuminate\Support\Facades\DB;
use Modules\Coupon\Entities\Coupon;
use Modules\Core\Facades\SiteConfig;
use Modules\Sales\Entities\OrderTax;
use Modules\Tax\Facades\NewTaxPrice;
use Modules\Tax\Facades\NewTaxPrices;
use Modules\Customer\Entities\Customer;
use Modules\Sales\Entities\OrderTaxItem;
use Intervention\Image\Exception\NotFoundException;
use Modules\CheckOutMethods\Traits\HasShippingCalculation;

trait HasOrderCalculation
{
    use HasShippingCalculation;

    protected float $discount_percent;
    protected float $shipping_amount;

    public function orderCalculationUpdate(object $order, object $request, object $coreCache): mixed
    {
        try
        {
            $sub_total = 0.00;
            $sub_total_tax_amount = 0.00;
            $total_qty_ordered = 0;
            $total_items = 0;
            foreach ( $order->order_items as $item ) {
                $sub_total += (float) $item->row_total;
                $sub_total_tax_amount += (float) $item->row_total_incl_tax;
                $total_qty_ordered += (float) $item->qty;
                $total_items += 1;
            }

            $discount_amount = (float) $this->calculateDiscount($order); // To-Do other discount will be added here...
            $arr_shipping_amount = [ "shipping_amount" => 0.00, "shipping_tax" => false ];

            $check_out_method_helper = $this->check_out_method_helper;
            if ($request->shipping_method) {
                $check_out_method_helper = new $check_out_method_helper($request->shipping_method);
                $arr_shipping_amount = $check_out_method_helper->process($request, ["order" => $order]);
            }
            //TODO:: move this fn to respective repo and this should on queue.
            $this->updateOrderCalculation($arr_shipping_amount, $order, $sub_total, $discount_amount, $sub_total_tax_amount, $total_qty_ordered, $total_items);
            $check_out_method_shipping_helper = new $check_out_method_helper($request->payment_method);
            $data = $check_out_method_shipping_helper->process($request, ["order" => $order]);

        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function calculateItems(object $order_item_details): array
    {
        try
        {
            $price = (float) $order_item_details->amount;
            $qty = (float) $order_item_details->qty;
            $weight = (float) $order_item_details->weight;
            $tax_amount = (float) $order_item_details->tax_amount;

            $tax_percent = (float) $order_item_details->tax_rate_percent;

            $price_incl_tax = (float) $order_item_details->amount_incl_tax;
            $row_total = (float) ($price * $qty);
            $row_total_incl_tax = (float) ($row_total + $tax_amount);
            $row_weight = (float) ($weight * $qty);

            $discount_amount_tax = 0.00; // this is total discount amount including tax
            $discount_amount = 0.00;
            $discount_percent = 0.00;

            $data = [
                "price" => $price,
                "qty" => $qty,
                "weight" => $weight,
                "tax_amount" => $tax_amount,
                "tax_percent" => $tax_percent,
                "price_incl_tax" => $price_incl_tax,
                "row_total" => $row_total,
                "row_total_incl_tax" => $row_total_incl_tax,
                "row_weight" => $row_weight,
                "discount_amount_tax" => $discount_amount_tax,
                "discount_amount" => $discount_amount,
                "discount_percent" => $discount_percent,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function calculateTax(object $request, array $order, object $coreCache): ?array
    {
        try
        {
            $get_zip_code = (!empty($request->get("address"))) ? $request->get("address")["shipping"] : '';
            $zip_code = isset($get_zip_code["postcode"]) ? $get_zip_code["postcode"] : null;
            $product_data = $this->getProductDetail($request, $order);
            $headers_data = $this->validateChangedCountry($request, $coreCache);
            $country_id = $headers_data["country_id"] ?? $get_zip_code["country_id"] ?? null;
            $region_id = $headers_data["region_id"] ?? $get_zip_code["region_id"] ?? null;
            $calculateTax = NewTaxPrices::calculate(
                request: $request,
                product_id: $product_data->product_id,
                country_id: $country_id,
                zip_code: $zip_code,
                region_id: $region_id,
            );
            $final_amount = $calculateTax["final"];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $final_amount;
    }

    public function calculateDiscount(object $order): mixed
    {
        try
        {
            if ($order->coupon_code) {
                $coupon = Coupon::whereCode($order->coupon_code)->publiclyAvailable()->first();
                if (!$coupon) throw new Exception("Coupon Expired");
                $this->discount_percent = $coupon->discount_percent;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $coupon ?? 0.00;
    }

    public function storeOrderTax(object $order, object $order_item_details, ?callable $callback = null): void
    {
        DB::beginTransaction();

        try
        {
            foreach ($order_item_details->rules as $rule) {
                $data = [
                    "order_id" => $order->id,
                    "code" => Str::slug($rule->name),
                    "title" => $rule->name,
                    "percent" => $order_item_details->tax_rate_percent,
                    "amount" => 0,
                ];
                $match = $data;
                unset($match["title"], $match["percent"], $match["amount"]);
                $order_tax = OrderTax::updateOrCreate($match, $data);
                if ($callback) {
                    $callback($order_tax, $order_item_details, $rule);
                }
            }
        }
        catch ( Exception $exception )
        {
            DB::rollback();
            throw $exception;
        }

        DB::commit();
    }

    public function storeOrderTaxItem(object $order_tax, object $order_item_details, mixed $rule): object
    {
        DB::beginTransaction();
        try
        {
            $data = [
                "tax_id" => $order_tax->id,
                "item_id" => $order_item_details->product_id,
                "tax_percent" => (float) $order_tax->percent,
                "amount" => ($rule->rates?->pluck("tax_rate_value")->first() * $order_item_details->qty),
                "tax_item_type" => "product",
            ];

            $match = $data;
            unset($match["tax_percent"], $match["amount"]);
            $order_tax_item = OrderTaxItem::updateOrCreate($match, $data);
        }
        catch ( Exception $exception )
        {
            DB::rollback();
            throw $exception;
        }

        DB::commit();
        return $order_tax_item;
    }

    public function updateOrderAddress(object $order): void
    {
        try
        {
            $addresses = $order->order_addresses()->get();
            $shipping_address = $addresses->where("address_type", "shipping")->first();
            $billing_address = $addresses->where("address_type", "billing")->first();
            $order->update([
                "customer_email" => $billing_address->email,
                "customer_first_name" => $billing_address->first_name,
                "customer_middle_name" => $billing_address?->middle_name,
                "customer_last_name" => $billing_address->last_name,
                "customer_phone" => $billing_address?->phone,
                "customer_taxvat" => $billing_address?->vat_number,
                "shipping_address_id" => $shipping_address->id,
                "billing_address_id" => $billing_address->id,
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    private function validateChangedCountry(object $request, object $coreCache): array
    {
        try
        {
            $country_id = !empty($request->header("country-id")) ?  $request->header("country-id") : null;
            $region_id = !empty($request->header("region-id")) ?  $request->header("region-id") : null;
            $channel_id = $coreCache->channel->id;
            $data = [
                "country_id" => $country_id,
                "region_id" => $region_id,
            ];
            if ($country_id) {
                $allow_countries =  SiteConfig::fetch("allow_countries", "channel", $channel_id)->pluck("id")->toArray();
                $default_country = SiteConfig::fetch("default_country", "channel", $channel_id)->id;
                $allow_countries[] = $default_country;
                if (!in_array($country_id, $allow_countries)) {
                    throw new NotFoundException(__("core::app.response.not-found", ["name" => "Country"]));
                }
                if ($region_id) {
                    $region = $this->regionRepository->queryFetch([
                        "id" => $request->header("region-id"),
                        "country_id" => $request->header("country-id"),
                    ]);
                    if (!$region) {
                        throw new NotFoundException(__("core::app.response.not-found", ["name" => "Region"]));
                    }
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }
}
