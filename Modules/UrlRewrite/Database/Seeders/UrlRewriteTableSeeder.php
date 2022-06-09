<?php

namespace Modules\UrlRewrite\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UrlRewriteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type_attributes = json_encode([
            "parameter" => [
                "category" => 1
            ]
        ]);
        DB::table("url_rewrites")->insert([
            [
                "type" => "Modules\Category\Entities\Category",
                "type_attributes" => $type_attributes,
                "request_path" => "root",
                "target_path" => "admin/catalog/categories/1",
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }
}
