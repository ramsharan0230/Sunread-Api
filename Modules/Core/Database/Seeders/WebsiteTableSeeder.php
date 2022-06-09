<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WebsiteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('websites')->insert([
            [
                "code" => "international",
                "hostname" => "international.co",
                "name" => "International",
                "description" => "For all countries",
                "position" => 1,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "code" => "national",
                "hostname" => "national",
                "name" => "national",
                "description" => "Not for all countries",
                "position" => 2,
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }
}
