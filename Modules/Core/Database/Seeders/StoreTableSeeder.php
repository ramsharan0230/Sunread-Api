<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Entities\Channel;

class StoreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('stores')->insert([
           [
               "name" => "International Store",
               "code" => "international-store",
               "position" => 1,
               "channel_id" => 1,
               "created_at" => now(),
               "updated_at" => now()
           ],
            [
                "name" => "English Store",
                "code" => "english-store",
                "position" => 2,
                "channel_id" => 1,
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }
}
