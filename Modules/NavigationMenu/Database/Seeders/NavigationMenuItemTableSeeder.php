<?php

namespace Modules\NavigationMenu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\NavigationMenu\Entities\NavigationMenuItem;

class NavigationMenuItemTableSeeder extends Seeder
{
    public function run(): void
    {
        NavigationMenuItem::factory()->create();
    }
}
