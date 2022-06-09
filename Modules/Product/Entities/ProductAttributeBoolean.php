<?php

namespace Modules\Product\Entities;

class ProductAttributeBoolean extends ProductAttributeType
{
    public static $type = "boolean";
    protected $table = "product_attribute_boolean";
}
