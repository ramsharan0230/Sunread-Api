<?php

namespace Modules\Country\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Country\Entities\Region;

class RegionTableSeeder extends Seeder
{
    public function run(): void
    {
        if ( in_array(app()->environment(), ["testing", "ci"]) ) {
            $regions = include("data/testing/regions.php");
        } else {
            $regions = include("data/regions.php");
        }

        $data = array_map(function ($region) {
            return [
                "country_id" => $region["country_id"],
                "code" => $region["country_code"].'-'.$region["code"],
                "name" => $region["name"],
                "created_at" => now(),
                "updated_at" => now()
            ];
        }, $regions);

        Region::insert($data);
    }
}
