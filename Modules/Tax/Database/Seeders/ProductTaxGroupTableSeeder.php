<?php

namespace Modules\Tax\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Tax\Entities\ProductTaxGroup;

class ProductTaxGroupTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        ProductTaxGroup::factory()->create();
    }
}
