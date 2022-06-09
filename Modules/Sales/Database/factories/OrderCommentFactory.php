<?php
namespace Modules\Sales\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Modules\Sales\Entities\Order;
use Modules\Sales\Entities\OrderComment;
use Modules\User\Entities\Admin;

class OrderCommentFactory extends Factory
{
    protected $model = \Modules\Sales\Entities\OrderComment::class;

    public function definition()
    {
        return [
            "order_id" => Order::first()->id,
            "user_id" => Admin::first()->id,
            "comment" => $this->faker->text(),
            "status_flag" => Arr::random(OrderComment::$status_flags),
        ];
    }
}

