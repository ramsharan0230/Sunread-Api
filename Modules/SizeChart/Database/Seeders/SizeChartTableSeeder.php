<?php

namespace Modules\SizeChart\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\SizeChart\Entities\SizeChart;
use Modules\SizeChart\Entities\SizeChartContent;
use Illuminate\Support\Str;

class SizeChartTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $sizeChart = SizeChart::factory()->create();
    }
}
