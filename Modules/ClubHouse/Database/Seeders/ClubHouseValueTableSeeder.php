<?php

namespace Modules\ClubHouse\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ClubHouse\Entities\ClubHouseValue;

class ClubHouseValueTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     */
    public function run(): void
    {
        $data = [
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "title",
                "value" => "Sail Racing Club House",
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "slug",
                "value" => "sail-racing-club-house"
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "status",
                "value" => 1
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "header_content",
                "value" => "Stop by a Sail Racing Club House to explore the latest technical sailing gear and our casual collections"
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "opening_hours",
                "value" => "Mon-Fri: 11-18"
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "address",
                "value" => "Bogota, Sweden"
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "contact",
                "value" => "98768908765"
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "latitude",
                "value" => "24.121212878"
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "latitude",
                "value" => "84.765467018"
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "thumbnail",
                "value" => null
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "background_type",
                "value" => null
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "background_image",
                "value" => null
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "meta_title",
                "value" => "Sail Racing Club House"
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "meta_description",
                "value" => "Stop by a Sail Racing Club House to explore the latest technical sailing gear and our casual collections"
            ],
            [
                "club_house_id" => 1,
                "scope" => "website",
                "scope_id" => 1,
                "attribute" => "meta_keywords",
                "value" => "sailracing,clubhouse,resort"
            ],
        ];

        $data = array_map(function($item) {
            return array_merge($item, [
                "value" => $item["value"],
                "created_at" => now(),
                "updated_at" => now()
            ]);
        }, $data);

        ClubHouseValue::insert($data);
    }
}
