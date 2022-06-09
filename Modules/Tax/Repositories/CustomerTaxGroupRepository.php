<?php

namespace Modules\Tax\Repositories;

use Modules\Tax\Entities\CustomerTaxGroup;
use Modules\Core\Repositories\BaseRepository;

class CustomerTaxGroupRepository extends BaseRepository
{
    public function __construct(CustomerTaxGroup $customerTaxGroup)
    {
        $this->model = $customerTaxGroup;
        $this->model_key = "customer-tax-groups";

        $this->rules = [
            "name" => "required",
            "description" => "required"
        ];
    }
}
