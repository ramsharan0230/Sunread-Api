<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Entities\Feature;

class FeatureTableSeeder extends Seeder
{
    public function run(): void
    {
        Feature::factory()->count(1)->create();
    }
}
