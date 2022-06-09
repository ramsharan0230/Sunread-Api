<?php

namespace Modules\SizeChart\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SizeChartDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $this->call(SizeChartTableSeeder::class);
        $this->call(SizeChartScopeTableSeeder::class);
        $this->call(SizeChartContentTableSeeder::class);
    }
}
