<?php

namespace Modules\Customer\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Customer\Entities\CustomerGroup;

class CustomerGroupRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new CustomerGroup();
        $this->model_key = "customers.groups";
        $this->model_name = "Customer Group";
        $this->rules = [
            "name" => "required|min:2|max:100",
            "slug" => "nullable|unique:customer_groups,slug",
            "customer_tax_group_id" => "required|exists:customer_tax_groups,id"
        ];
//        $this->restrict_default_delete = true;
    }
}
