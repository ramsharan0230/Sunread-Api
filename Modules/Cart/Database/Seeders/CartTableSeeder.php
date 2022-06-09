<?php

namespace Modules\Cart\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Cart\Entities\Cart;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;

class CartTableSeeder extends Seeder
{
    public function run(): void
    {
        Cart::create([
            "item_count" => 1,
            "total_quantity" => 1,
            "channel_code" => Channel::first()->code,
            "store_code" => Store::first()->code
        ]);
    }
}
