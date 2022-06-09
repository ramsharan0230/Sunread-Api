<?php
namespace Modules\Cart\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Cart\Entities\Cart;
use Modules\Cart\Entities\CartItem;
use Modules\Product\Entities\Product;

class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            "cart_id" => Cart::factory()->create()->id,
            "product_id" => Product::first()->id,
            "qty" => random_int(1,10),
        ];
    }
}

