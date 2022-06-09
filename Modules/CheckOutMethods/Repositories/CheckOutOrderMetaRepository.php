<?php

namespace Modules\CheckOutMethods\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Sales\Entities\OrderMeta;

class CheckOutOrderMetaRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new OrderMeta();
        $this->model_key = "order_meta";
    }
}