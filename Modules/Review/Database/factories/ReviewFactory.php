<?php
namespace Modules\Review\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\Product;

class ReviewFactory extends Factory
{
    protected $model = \Modules\Review\Entities\Review::class;

    public function definition(): array
    {
        $customer = Customer::latest("id")->first();
        $product = Product::latest("id")->first();

        return [
            "customer_id" => $customer->id,
            "product_id" => $product->id,
            "rating" => rand(1,5),
            "title" => $this->faker->name(),
            "description" => $this->faker->paragraph()
        ];
    }
}
