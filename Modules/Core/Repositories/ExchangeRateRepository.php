<?php

namespace Modules\Core\Repositories;

use Modules\Core\Entities\ExchangeRate;

class ExchangeRateRepository extends BaseRepository
{
    public function __construct(ExchangeRate $exchangeRate)
    {
        $this->model = $exchangeRate;
        $this->model_key = "core.exchange_rates";
        $this->rules = [
            /* General */
            "rate" => "required|numeric",
           
            /* Foreign Keys */
            "source_currency" => "required|exists:currencies,id",
            "target_currency" => "required|exists:currencies,id"
        ];
    }
}
