<?php

namespace Modules\Country\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CountryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        $this->call(CountryTableSeeder::class);
        $this->call(RegionTableSeeder::class);
        $this->call(CityTableSeeder::class);
    }
}
