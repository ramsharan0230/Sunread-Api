<?php

namespace Modules\Erp\Repositories;

use Modules\Erp\Entities\NavErpOrderMapper;
use Modules\Core\Repositories\BaseRepository;


class NavErpOrderMapperRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new NavErpOrderMapper();
        $this->model_key = "erp.mappers";

        $this->rules = [
            "website_id" => "required|numeric|exists:websites,id",
            "title" => "required|string|max:255",
            "nav_customer_number" => "required|string|max:50",
            "shipping_account" => "required|string|max:50",
            "discount_account" => "required|string|max:50",
            "customer_price_group" => "required|string|max:50",
            "is_default" => "boolean",
        ];
    }
}

