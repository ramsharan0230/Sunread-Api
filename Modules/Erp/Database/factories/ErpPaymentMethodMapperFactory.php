<?php
namespace Modules\Erp\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Entities\Website;
use Illuminate\Support\Str;

class ErpPaymentMethodMapperFactory extends Factory
{
    protected $model = \Modules\Erp\Entities\ErpPaymentMethodMapper::class;

    public function definition(): array
    {
        return [
            "website_id" => Website::first()->id,
            "payment_method" => Str::random(4),
            "payment_method_code" => Str::random(7),
        ];
    }
}