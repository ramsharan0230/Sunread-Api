<?php

namespace Modules\Sales\Database\Seeders;

use Illuminate\Database\Seeder;

class SalesDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(OrderStatusStateTableSeeder::class);
        $this->call(OrderStatusTableSeeder::class);
        $this->call(OrderTableSeeder::class);
        $this->call(OrderItemTableSeeder::class);
        $this->call(OrderTaxTableSeeder::class);
        $this->call(OrderTaxItemTableSeeder::class);
        $this->call(OrderCommentTableSeeder::class);
    }
}
