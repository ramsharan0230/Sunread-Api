<?php
namespace Modules\Product\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductAttributeStringFactory extends Factory
{
    protected $model = \Modules\Product\Entities\ProductAttributeString::class;

    public function definition(): array
    {
        return [
            "value" => $this->faker->name()
        ];
    }
}
