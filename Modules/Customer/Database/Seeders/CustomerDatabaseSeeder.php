<?php

namespace Modules\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CustomerDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        $this->call(CustomerGroupTableSeeder::class);
        $this->call(CustomerTableSeeder::class);
        $this->call(CustomerAddressTableSeeder::class);
    }
}
