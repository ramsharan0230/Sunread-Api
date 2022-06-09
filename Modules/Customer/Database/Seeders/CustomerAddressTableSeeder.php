<?php

namespace Modules\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomerAddress;

class CustomerAddressTableSeeder extends Seeder
{
    public function run(): void
    {
        CustomerAddress::factory()->create();
    }
}
