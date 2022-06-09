<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ImageTypeTableSeeder extends Seeder
{

    public function run()
    {
        DB::table("image_types")->insert([
            [
                "name" => "Base Image",
                "slug" => "base_image",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "name" => "Small Image",
                "slug" => "small_image",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "name" => "thumbnail_image",
                "slug" => "thumbnail_image",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "name" => "Section Background Image",
                "slug" => "section_background_image",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "name" => "Gallery",
                "slug" => "gallery",
                "created_at" => now(),
                "updated_at" => now()
            ],
        ]);
    }
}
