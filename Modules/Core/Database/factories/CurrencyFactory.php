<?php
namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Entities\Currency;

class CurrencyFactory extends Factory
{
    protected $model = \Modules\Core\Entities\Currency::class;

    public function definition(): array
    {
        while(true) {
            $currency = $this->faker->unique()->currencyCode();
            $old_currency = Currency::whereCode($currency)->first();
            if (!$old_currency) break;
        }

        return [
            "code" => $currency,
            "name" => $currency,
            "symbol" => $currency
        ];
    }
}
