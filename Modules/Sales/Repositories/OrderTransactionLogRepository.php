<?php

namespace Modules\Sales\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Sales\Entities\OrderTransactionLog;

class OrderTransactionLogRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new OrderTransactionLog();
        $this->model_key = "order_transaction_log";
    }
}
