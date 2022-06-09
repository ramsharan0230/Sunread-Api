<?php
namespace Modules\Product\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductAttributeDecimalFactory extends Factory
{
    protected $model = \Modules\Product\Entities\ProductAttributeDecimal::class;

    public function definition(): array
    {
        return [
            "value" => $this->faker->randomFloat(2, 100, 99999)
        ];
    }
}
