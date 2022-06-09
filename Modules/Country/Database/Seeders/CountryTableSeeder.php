<?php

namespace Modules\Country\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryTableSeeder extends Seeder
{
    public function run(): void
    {
        if ( in_array(app()->environment(), ["testing", "ci"]) ) {
            $countries = include("data/testing/countries.php");
        } else {
            $countries = include("data/countries.php");
        }

        $data = array_map(function ($country) {
            return [
                "iso_2_code" => $country["iso_2_code"],
                "iso_3_code" => $country["iso_3_code"],
                "numeric_code" => $country["numeric_code"],
                "dialing_code" => $country["dialing_code"],
                "name" => $country["name"],
                "created_at" => now(),
                "updated_at" => now()
            ];
        }, $countries);

        DB::table("countries")->insert($data);
    }
}
