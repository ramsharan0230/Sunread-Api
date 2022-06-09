<?php

namespace Modules\NavigationMenu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class NavigationMenuDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $this->call(NavigationMenuTableSeeder::class);
        $this->call(NavigationMenuItemTableSeeder::class);
        $this->call(NavigationMenuItemValueTableSeeder::class);
    }
}
