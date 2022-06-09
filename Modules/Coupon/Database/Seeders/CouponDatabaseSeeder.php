<?php

namespace Modules\Coupon\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CouponDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $this->call(CouponTableSeeder::class);
    }
}
