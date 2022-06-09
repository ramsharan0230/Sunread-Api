<?php

namespace Modules\Sales\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Entities\Store;
use Modules\Sales\Entities\Order;
use Illuminate\Support\Facades\DB;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Website;

class OrderItemTableSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                "website_id" => Website::first()?->id,
                "channel_id" => Channel::first()?->id,
                "store_id" => Store::first()?->id,
                "product_id" => 1,
                "order_id" => Order::first()?->id,
                "product_options" => json_encode([
                    "attributes" => [
                        [
                            "attribute_id" => 24,
                            "label" => "size",
                            "name" => "Size",
                            "value" => "L",
                        ],
                        [
                            "attribute_id" => 23,
                            "label" => "color",
                            "name" => "Color",
                            "value" => "red",
                        ],
                    ],
                    "image_url" => url("product.png"),
                ]),
                "product_type" => "simple",
                "sku" => "dell-laptop",
                "name" => "Dell laptop Bag",
                "qty" => 10.00,
                "cost" => 10.00,
                "price" => 10.00,
                "price_incl_tax" => 11.3,
                "discount_percent" => 5.00,
                "discount_amount" => 0.5,
                "discount_amount_tax" => 0.565,
                "tax_amount" => 1.3,
                "tax_percent" => 13,
                "row_total" => 100,
                "row_total_incl_tax" => 100.735,
                "row_weight" => 150.1,
            ],
        ];

        DB::table("order_items")->insert($data);
    }
}
