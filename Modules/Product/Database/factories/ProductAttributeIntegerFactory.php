<?php
namespace Modules\Product\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductAttributeIntegerFactory extends Factory
{
    protected $model = \Modules\Product\Entities\ProductAttributeInteger::class;

    public function definition(): array
    {
        return [
            "value" => $this->faker->randomDigit()
        ];
    }
}
