<?php
namespace Modules\Product\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductAttributeBooleanFactory extends Factory
{
    protected $model = \Modules\Product\Entities\ProductAttributeBoolean::class;

    public function definition(): array
    {
        return [
            "value" => rand(0, 1)
        ];
    }
}
