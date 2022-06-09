<?php

namespace Modules\Sales\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Sales\Entities\OrderLog;

class OrderLogRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new OrderLog();
        $this->model_key = "order_logs";
    }
}
