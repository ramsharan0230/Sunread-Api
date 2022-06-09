<?php

namespace Modules\SizeChart\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\SizeChart\Entities\SizeChartScope;

class SizeChartScopeTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        SizeChartScope::factory()->create();
    }
}
