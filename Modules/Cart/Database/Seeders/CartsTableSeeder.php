<?php

namespace Modules\Cart\Database\Seeders;

use Illuminate\Database\Seeder;

class CartsTableSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CartTableSeeder::class);
        $this->call(CartItemTableSeeder::class);
    }
}
