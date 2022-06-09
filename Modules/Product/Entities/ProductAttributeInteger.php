<?php

namespace Modules\Product\Entities;

class ProductAttributeInteger extends ProductAttributeType
{
    public static $type = "integer";
    protected $table = "product_attribute_integer";
}
