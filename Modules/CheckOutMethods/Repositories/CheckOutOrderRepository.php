<?php

namespace Modules\CheckOutMethods\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Sales\Entities\Order;

class CheckOutOrderRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Order();
        $this->model_key = "order";
    }

}