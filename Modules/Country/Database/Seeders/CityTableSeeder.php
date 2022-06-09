<?php

namespace Modules\Country\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CityTableSeeder extends Seeder
{
    public function run(): void
    {
        if ( in_array(app()->environment(), ["testing", "ci"]) ) {
            $cities = include("data/testing/cities.php");
        } else {
            $cities = include("data/cities.php");
        }

        $data = array_map(function ($city) {
            return [
                "region_id" => $city["region_id"],
                "postal_code" => $city["postal_code"] ?? null,
                "code" => $city["code"] ?? null,
                "name" => $city["name"],
                "created_at" => now(),
                "updated_at" => now()
            ];
        }, $cities);

        $chunks = array_chunk($data, 5000);
        foreach ($chunks as $chunk) {
            DB::table("cities")->insert($chunk);
        }
    }
}
