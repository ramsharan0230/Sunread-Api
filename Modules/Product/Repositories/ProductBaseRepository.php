<?php

namespace Modules\Product\Repositories;

use Modules\Product\Entities\Product;
use Modules\Core\Repositories\BaseRepository;

class ProductBaseRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Product();
        $this->model_key = "catalog.products";
    }
}
