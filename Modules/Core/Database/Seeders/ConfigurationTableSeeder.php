<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConfigurationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('configurations')->insert([
            [
                "scope" => "global",
                "scope_id" => 0,
                "path" => "default_country",
                "value" => json_encode("SE"),
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }
}
