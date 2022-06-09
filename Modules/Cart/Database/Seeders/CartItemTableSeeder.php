<?php

namespace Modules\Cart\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Cart\Entities\Cart;
use Modules\Product\Entities\Product;

class CartItemTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table("cart_items")->insert([
            "product_id" => Product::first()->id,
            "cart_id" => Cart::first()->id,
            "qty" => 10,
            "created_at" => now(),
            "updated_at" => now()
        ]);
    }
}
