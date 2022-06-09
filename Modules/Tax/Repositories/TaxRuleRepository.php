<?php

namespace Modules\Tax\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Tax\Entities\TaxRule;

class TaxRuleRepository extends BaseRepository
{
    public function __construct(TaxRule $taxRule)
    {
        $this->model = $taxRule;
        $this->model_key = "tax-rules";
        $this->rules = [
            "customer_tax_groups" => "sometimes|array",
            "customer_tax_groups.*" => "exists:customer_tax_groups,id",
            "product_tax_groups" => "sometimes|array",
            "product_tax_groups.*" => "exists:product_tax_groups,id",
            "name" => "required",
            "priority" => "required|numeric",
            "tax_rates" => "sometimes|array",
            "tax_rates.*" => "exists:tax_rates,id"
        ];
    }
}
