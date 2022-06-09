<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalesTableSeeder extends Seeder
{
    public function run()
    {
        if ( in_array(app()->environment(), ["testing", "ci"]) ) {
            $locales = include("data/testing/locales.php");
        } else {
            $locales = include("data/locales.php");
        }

        $data = array_map(function ($locale) {
            return [
                "code" => $locale["code"],
                "name" => $locale["name"],
                "created_at" => now(),
                "updated_at" => now()
            ];
        }, $locales);

        $chunks = array_chunk($data, 100);
        foreach ($chunks as $chunk) {
            DB::table("locales")->insert($chunk);
        }
    }
}
