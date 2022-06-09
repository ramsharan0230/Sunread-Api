<?php

namespace Modules\Product\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Product\Entities\AttributeOptionsChildProduct;

class AttributeOptionsChildProductRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new AttributeOptionsChildProduct();
        $this->model_key = "catalog.attributeOptions.childProducts";
    }
}
