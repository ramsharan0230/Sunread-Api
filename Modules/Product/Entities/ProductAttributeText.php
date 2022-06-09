<?php

namespace Modules\Product\Entities;

class ProductAttributeText extends ProductAttributeType
{
    public static $type = "string";
    protected $table = "product_attribute_text";
}
