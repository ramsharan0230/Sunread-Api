<?php

namespace Modules\Page\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PageDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        $this->call(PageTableSeeder::class);
    }
}
