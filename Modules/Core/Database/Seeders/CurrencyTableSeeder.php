<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CurrencyTableSeeder extends Seeder
{
    public function run(): void
    {
        $currency_data = config("currencies");
        $currencies = [];
        foreach ($currency_data as $currency)
        {
            $currencies[] = array_merge($currency, [
                "is_default" => ($currency["code"] == "SEK") ? 1 : 0,
                "created_at" => now(),
                "updated_at" => now()
            ]);
        }
        DB::table('currencies')->insert($currencies);
    }
}
