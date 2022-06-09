<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChannelTableSeeder extends Seeder
{
    /**
    * Run the database seeds.
    *
    * @return void
    */
    public function run()
    {
        DB::table('channels')->insert([
            [
                "name" => "International",
                "code" => "international",
                "hostname" => "international.xyz.co",
                "description" => "For all countries",
                "website_id" => 1,
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }
}
