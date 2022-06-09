<?php

namespace Modules\Erp\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Erp\Entities\ErpLog;

class ErpLogRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new ErpLog();
        $this->model_name = "ErpLogs";
        $this->model_key = "ErpLogs";
    }
}
