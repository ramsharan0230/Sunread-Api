<?php

namespace Modules\SizeChart\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\SizeChart\Entities\SizeChartContent;

class SizeChartContentTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        SizeChartContent::factory()->create();
    }
}
