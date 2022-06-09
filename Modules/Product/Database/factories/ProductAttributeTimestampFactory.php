<?php
namespace Modules\Product\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductAttributeTimestampFactory extends Factory
{
    protected $model = \Modules\Product\Entities\ProductAttributeTimestamp::class;

    public function definition()
    {
        return [
            "value" => now()
        ];
    }
}
