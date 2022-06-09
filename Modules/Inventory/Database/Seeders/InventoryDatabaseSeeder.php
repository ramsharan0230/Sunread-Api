<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class InventoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        $this->call(CatalogInventoryTableSeeder::class);
    }
}
