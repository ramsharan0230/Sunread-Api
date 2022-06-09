<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ProductDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $this->call(ProductTableSeeder::class);
        $this->call(ImageTypeTableSeeder::class);
        $this->call(FeatureTableSeeder::class);
    }
}
