<?php

namespace Modules\Product\Entities;

use Modules\Core\Traits\Sluggable;

class ProductAttributeString extends ProductAttributeType
{
    use Sluggable;
    
    public static $type = "string";
    protected $table = "product_attribute_string";
}
