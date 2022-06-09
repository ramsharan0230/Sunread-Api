<?php

namespace Modules\Product\Entities;

class ProductAttributeTimestamp extends ProductAttributeType
{
    public static $type = "date";
    protected $table = "product_attribute_timestamp";
}
