<?php
namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Entities\Currency;

class ExchangeRateFactory extends Factory
{
    protected $model = \Modules\Core\Entities\ExchangeRate::class;

    public function definition(): array
    {
        $source = Currency::factory()->create();
        $target = Currency::factory()->create();

        return [
            'source_currency' => $source->id,
            'target_currency' => $target->id,
            'rate' => rand(0,10)
        ];
    }
}
