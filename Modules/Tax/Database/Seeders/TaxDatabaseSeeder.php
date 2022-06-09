<?php

namespace Modules\Tax\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TaxDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $this->call(TaxRateTableSeeder::class);
        $this->call(CustomerTaxGroupTableSeeder::class);
        $this->call(ProductTaxGroupTableSeeder::class);
        $this->call(TaxRuleTableSeeder::class);
    }
}
