<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Core\Entities\Website;
use Modules\Product\Entities\Product;

class CatalogInventoryTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table("catalog_inventories")->insert([
            "product_id" => Product::first()->id,
            "website_id" => Website::first()->id,
            "quantity" => 10,
            "manage_stock" => 1,
            "is_in_stock" => 1,
            "created_at" => now(),
            "updated_at" => now()
        ]);
    }
}
