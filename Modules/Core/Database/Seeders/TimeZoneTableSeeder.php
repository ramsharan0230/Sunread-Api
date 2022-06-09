<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TimeZoneTableSeeder extends Seeder
{
    public function run()
    {
        $time_zones = [];
        $time_zone_data = config("time_zones");
        foreach ($time_zone_data as $time_zone)
        {
            $time_zones[] = array_merge($time_zone, [
                "created_at" => now(),
                "updated_at" => now()
            ]);
        }
        
        DB::table('time_zones')->insert($time_zones);
    }
}
