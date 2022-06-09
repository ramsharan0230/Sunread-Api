<?php

namespace Modules\Sales\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Sales\Entities\OrderTax;

class OrderTaxRepositroy extends BaseRepository
{
    public function __construct()
    {
        $this->model = new OrderTax();
        $this->model_key = "order_tax";
    }
}
