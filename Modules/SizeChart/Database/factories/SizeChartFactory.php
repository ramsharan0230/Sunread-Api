<?php
namespace Modules\SizeChart\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Entities\Website;

class SizeChartFactory extends Factory
{
    protected $model = \Modules\SizeChart\Entities\SizeChart::class;

    public function definition(): array
    {
        return [
            "title" => $this->faker->name(),
            "slug" => $this->faker->unique()->slug(),
            "content" => $this->faker->text(),
            "website_id" => Website::inRandomOrder()->first()->id,
            "status" => 1,
        ];
    }
}
