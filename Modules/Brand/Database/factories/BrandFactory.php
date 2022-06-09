<?php
namespace Modules\Brand\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    protected $model = \Modules\Brand\Entities\Brand::class;

    public function definition(): array
    {
        $name = $this->faker->name();
        $slug = $this->faker->unique()->slug();

        return [
            "name" => $name,
            "slug" => $slug,
            "description" => $this->faker->paragraph()
        ];
    }
}
