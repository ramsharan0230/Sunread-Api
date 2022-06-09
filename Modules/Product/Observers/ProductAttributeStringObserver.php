<?php


namespace Modules\Product\Observers;

use Modules\Product\Entities\ProductAttributeString;
use Modules\UrlRewrite\Facades\UrlRewrite;

class ProductAttributeStringObserver
{
    public function updated(ProductAttributeString $product_attribute_string)
    {
        // UrlRewrite::handleUrlRewrite($product_attribute_string->product_attribute, __FUNCTION__);
    }
}
