<?php

namespace Modules\Product\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureFactory extends Factory
{
    protected $model = \Modules\Product\Entities\Feature::class;

    public function definition(): array
    {
        return [
            "name" => $this->faker->name(),
            "description" => $this->faker->paragraph(),
        ];
    }
}
