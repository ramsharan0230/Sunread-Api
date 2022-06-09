<?php

namespace Modules\Sales\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Sales\Entities\Order;

class OrderTaxTableSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            "order_id" => Order::first()?->id,
            "code" => "tax-rule",
            "title" => "Tax Rule",
            "percent" => 13,
            "amount" => 1.3
        ];

        DB::table("order_taxes")->insert($data);
    }
}
