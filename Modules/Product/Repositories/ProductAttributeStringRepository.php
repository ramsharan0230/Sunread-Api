<?php

namespace Modules\Product\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Product\Entities\ProductAttributeString;

class ProductAttributeStringRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new ProductAttributeString();
        $this->model_key = "catalog.products.attribute.options";
    }
}
