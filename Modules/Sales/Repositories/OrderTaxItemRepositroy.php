<?php

namespace Modules\Sales\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Sales\Entities\OrderTaxItem;

class OrderTaxItemRepositroy extends BaseRepository
{
    public function __construct()
    {
        $this->model = new OrderTaxItem();
        $this->model_key = "order_tax_item";
    }
}
