<?php

namespace Modules\Tax\Services;

use Exception;
use Modules\Core\Facades\CoreCache;
use Modules\Product\Repositories\ProductBaseRepository;
use Modules\Tax\Facades\NewTaxPrices;

class GlobalCalculation {

    public function getConfigValue(
        object $request, 
        array $products, 
        ?int $customer_tax_group_id = 1,
        ?int $country_id = null,
        ?string $zip_code = null,
        ?int $region_id = null
    ): object {
        try
        {
            $calculated_products = [];
            $fetched = [];

            $total_qty = 0;
            $sub_total = 0;
            $total_tax_amt = 0;

            foreach ($products as $product) {

                $productBaseRepository = new ProductBaseRepository();
                $product = $productBaseRepository->fetch($product["id"]);
                if(!$product) continue;

                $tax_data = NewTaxPrices::calculate($request, $product->id, $country_id, $zip_code, $region_id);

                $website = CoreCache::getWebsite($request->header("hc-host"));
                $channel = CoreCache::getChannel($website, $request->header("hc-channel"));
                $store = CoreCache::getStore($website, $channel, $request->header("hc-store"));

                $qty = $product["qty"];
                $weight = $product->value([
                    "scope" => "store",
                    "scope_id" => $store->id,
                    "attribute_slug" => "weight",
                ]);
                $final_data = $tax_data["price"]["final"];
                $amount = $final_data["amount"];
                $tax_amt = $final_data["tax_amount"];
                $amount_incl_tax = $final_data["amount_incl_tax"];

                $calculate_data = [
                    "id" => $product["id"],
                    "qty" => $qty,
                    "weight" => $weight,
                    "total_weight" => $qty * $weight,
                    "unit_price" => $amount,
                    "unit_tax_amount" => $tax_amt,
                    "tax_rate_percent" => $final_data["tax_rate_percent"],
                    "unit_price_including_tax" => $amount_incl_tax,
                    "total_price" => $qty * $amount,
                    "total_tax_amount" => $qty * $tax_amt,
                    "total_price_including_tax" => $qty * $amount_incl_tax,
                    "tax_item_type" => "product",
                    "rules" => $final_data["rules"],
                ];

                $total_qty += $qty ;
                $sub_total += $calculate_data["total_price"];
                $total_tax_amt += $calculate_data["total_tax_amount"];

                $calculated_products[] = $calculate_data;
            }

            $fetched = [
                "products" => $calculated_products,
                "total_calculation" => [
                    "total_product" => count($calculated_products),
                    "total_qty" => $total_qty,
                    "sub_total" => $sub_total,
                    "total_tax_amt" => $total_tax_amt,
                    "grand_total" => $sub_total + $total_tax_amt
                ],
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (object) $fetched;
    }
}
