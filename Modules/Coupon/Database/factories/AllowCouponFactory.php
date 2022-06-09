<?php
namespace Modules\Coupon\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Modules\Coupon\Entities\AllowCoupon;
use Modules\Coupon\Entities\Coupon;

class AllowCouponFactory extends Factory
{
    protected $model = AllowCoupon::class;

    public function definition(): array
    {
        $model_type = Arr::random(config('model_list.model_types'));
        return [
            "coupon_id" => Coupon::factory()->create()->id,
            "model_type" => $model_type,
            "model_id" => rand(1, 10),
            "status" => 1
        ];
    }
}

