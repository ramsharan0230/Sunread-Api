<?php

namespace Modules\Brand\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BrandTableSeeder extends Seeder
{
    public function run()
    {
        DB::table("brands")->insert([
            [
                "name" => "Fancy Wears",
                "slug" => "fancy-wears",
                "description" => "This is a fancy wear.",
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }
}
