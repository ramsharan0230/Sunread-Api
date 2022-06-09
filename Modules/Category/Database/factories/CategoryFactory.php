<?php
namespace Modules\Category\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Entities\Website;

class CategoryFactory extends Factory
{
    protected $model = \Modules\Category\Entities\Category::class;

    public function definition(): array
    {

        return [
            "position" => $this->faker->randomDigit(),
            "parent_id" => null,
            "website_id" => Website::factory()->create()->id
        ];
    }
}

