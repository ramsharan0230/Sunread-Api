<?php

namespace Modules\Erp\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Entities\ErpShippingAttributeMapper;

class ShippingAttributeMapperTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        ErpShippingAttributeMapper::factory()->create();
    }
}