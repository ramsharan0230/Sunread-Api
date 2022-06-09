<?php

namespace Modules\Erp\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Entities\NavErpOrderMapper;

class NavErpOrderMapperTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        NavErpOrderMapper::factory()->create();
    }
}