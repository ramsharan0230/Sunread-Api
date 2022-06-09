<?php

namespace Modules\Coupon\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Coupon\Entities\Coupon;

class CouponTableSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::factory()->count(10)->create();
    }
}
