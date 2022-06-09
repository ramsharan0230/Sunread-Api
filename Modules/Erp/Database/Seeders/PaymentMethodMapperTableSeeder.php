<?php

namespace Modules\Erp\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Entities\ErpPaymentMethodMapper;

class PaymentMethodMapperTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        ErpPaymentMethodMapper::factory()->create();
    }
}