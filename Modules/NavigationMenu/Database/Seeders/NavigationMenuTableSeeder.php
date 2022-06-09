<?php

namespace Modules\NavigationMenu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\NavigationMenu\Entities\NavigationMenu;

class NavigationMenuTableSeeder extends Seeder
{
    public function run(): void
    {
        NavigationMenu::factory()->create();
    }
}
