<?php

namespace Modules\Erp\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ErpDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $this->call(ErpImportSeederTableSeeder::class);
        $this->call(NavErpOrderMapperTableSeeder::class);
        $this->call(ShippingAttributeMapperTableSeeder::class);
        $this->call(PaymentMethodMapperTableSeeder::class);

    }
}
