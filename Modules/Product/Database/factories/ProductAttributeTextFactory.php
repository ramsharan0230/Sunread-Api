<?php
namespace Modules\Product\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductAttributeTextFactory extends Factory
{
    protected $model = \Modules\Product\Entities\ProductAttributeText::class;

    public function definition(): array
    {
        return [
            "value" => $this->faker->paragraph()
        ];
    }
}
