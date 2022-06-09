<?php

namespace Modules\Core\Repositories;

use Modules\Core\Entities\Currency;
use Modules\Core\Repositories\BaseRepository;

class CurrencyRepository extends BaseRepository
{
    public function __construct(Currency $currency)
    {
        $this->model = $currency;
        $this->model_key = "core.currencies";
        $this->rules = [
            "code" => "required|min:3|max:3|unique:currencies,code",
            "name" => "required",
            "symbol" => "required"
        ];
    }
}
