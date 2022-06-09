<?php

namespace Modules\Tax\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Tax\Entities\TaxRate;
use Illuminate\Database\Eloquent\Model;

class TaxRateTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        TaxRate::factory()->create();
    }
}
