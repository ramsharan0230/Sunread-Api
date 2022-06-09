<?php

namespace Modules\Sales\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Sales\Entities\Order;
use Modules\Sales\Entities\OrderTax;

class OrderTaxItemTableSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                "tax_id" => OrderTax::first()?->id,
                "item_id" => Product::first()?->id,
                "tax_percent" => 13.00,
                "amount" => 1.3,
                "tax_item_type" => "product"
            ],
        ];

        DB::table("order_tax_items")->insert($data);
    }
}
