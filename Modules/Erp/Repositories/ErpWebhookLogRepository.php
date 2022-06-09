<?php

namespace Modules\Erp\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Erp\Entities\ErpWebhookLog;

class ErpWebhookLogRepository extends BaseRepository
{
    protected string $base_url;

    public function __construct()
    {
        $this->model = new ErpWebhookLog();
        $this->model_name = "ErpWebhookLog";
        $this->model_key = "ErpWebhookLog";

    }
}
