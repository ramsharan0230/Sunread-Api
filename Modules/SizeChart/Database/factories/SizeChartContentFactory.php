<?php
namespace Modules\SizeChart\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SizeChart\Entities\SizeChartContent;
use Modules\SizeChart\Entities\SizeChart;
use Illuminate\Support\Arr;

class SizeChartContentFactory extends Factory
{
    protected $model = \Modules\SizeChart\Entities\SizeChartContent::class;

    public function definition(): array
    {
        return [
            "title" => $this->faker->name(),
            "slug" => $this->faker->unique()->slug(),
            "type" => Arr::random(SizeChartContent::$content_types),
            "content" => json_encode($this->faker->name),
            "size_chart_id" => SizeChart::inRandomOrder()->first()->id,
        ];
    }
}