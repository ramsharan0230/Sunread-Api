<?php

namespace Modules\Sales\Repositories;

use Exception;
use Modules\Sales\Entities\OrderMeta;
use Modules\Core\Repositories\BaseRepository;

class OrderMetaRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new OrderMeta();
        $this->model_key = "order_metas";
        $this->rules = [
            "shipping_method" => "required",
            "payment_method" => "required",
        ];
    }
}
