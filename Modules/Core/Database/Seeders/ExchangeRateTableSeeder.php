<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ExchangeRateTableSeeder extends Seeder
{
    public function run()
    {
        DB::table("exchange_rates")->insert([
            [
                "id" => 1,
                "source_currency" => 1,
                "target_currency" => 2,
                "rate" => 2.4,
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }
}