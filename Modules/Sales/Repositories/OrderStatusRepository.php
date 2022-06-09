<?php

namespace Modules\Sales\Repositories;

use Modules\Sales\Entities\OrderStatus;
use Modules\Core\Repositories\BaseRepository;

class OrderStatusRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new OrderStatus();
        $this->model_key = "order_statuses";
        $this->rules = [
            "name" => "required",
            "state_id" => "required|exists:order_status_states,id"
        ];
    }
}
