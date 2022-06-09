<?php

namespace Modules\Cart\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Cart\Entities\Cart;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            "item_count" => random_int(1,10),
            "total_quantity" => random_int(1,10),
        ];
    }
}

