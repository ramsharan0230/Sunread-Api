<?php

namespace Modules\Erp\Repositories;

use Modules\Erp\Entities\ErpShippingAttributeMapper;
use Modules\Core\Repositories\BaseRepository;


class ShippingAttributeMapperRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new ErpShippingAttributeMapper();
        $this->model_key = "erp.mappers.attributes";

        $this->rules = [
            "website_id" => "required|numeric|exists:websites,id",
            "shipping_agent_code" => "required|string|max:255",
            "shipping_agent_service_code" => "required|string|max:255",
            "shipping_method_code" => "required|string|max:255",
        ];
    }
}

