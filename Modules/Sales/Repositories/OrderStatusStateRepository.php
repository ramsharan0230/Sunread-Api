<?php

namespace Modules\Sales\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Sales\Entities\OrderStatusState;

class OrderStatusStateRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new OrderStatusState();
        $this->model_key = "order_status_states";
    }
}
