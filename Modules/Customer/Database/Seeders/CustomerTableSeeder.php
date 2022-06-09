<?php

namespace Modules\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;

class CustomerTableSeeder extends Seeder
{
    public function run(): void
    {
        Customer::factory()->create();
    }
}
