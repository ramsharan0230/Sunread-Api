<?php
namespace Modules\SizeChart\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SizeChart\Entities\SizeChart;
use Modules\SizeChart\Entities\SizeChartScope;

class SizeChartScopeFactory extends Factory
{
    protected $model = SizeChartScope::class;

    public function definition(): array
    {
        return [
            "size_chart_id" => SizeChart::inRandomOrder()->first()->id,
            "scope" => "store",
            "scope_id" => 0,
        ];
    }
}