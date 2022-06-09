<?php

namespace Modules\Product\Entities;

class ProductAttributeDecimal extends ProductAttributeType
{
    public static $type = "decimal";
    protected $table = "product_attribute_decimal";

}
