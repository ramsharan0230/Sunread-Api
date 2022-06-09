<?php

namespace Modules\Tax\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Tax\Entities\CustomerTaxGroup;

class CustomerTaxGroupTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        CustomerTaxGroup::factory()->create();
    }
}
