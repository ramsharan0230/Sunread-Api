<?php

namespace Modules\ClubHouse\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ClubHouseDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     */
    public function run(): void
    {
        Model::unguard();
        $this->call(ClubHouseTableSeeder::class);
        $this->call(ClubHouseValueTableSeeder::class);
    }
}
