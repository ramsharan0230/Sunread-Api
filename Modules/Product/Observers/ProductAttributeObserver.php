<?php


namespace Modules\Product\Observers;

use Modules\Product\Entities\ProductAttribute;
use Modules\UrlRewrite\Facades\UrlRewrite;

class ProductAttributeObserver
{
    public function created(ProductAttribute $product_attribute)
    {
        // if($product_attribute->attribute->name === 'Slug' && isset($product_attribute->value_id)) UrlRewrite::handleUrlRewrite($product_attribute, __FUNCTION__);
    }

    public function updated(ProductAttribute $product_attribute)
    {
        // if($product_attribute->attribute->name === 'Slug' && isset($product_attribute->value_id))
        // {
        //     if($product_attribute->getUrlRewrite()) UrlRewrite::handleUrlRewrite($product_attribute, __FUNCTION__);
        //     else UrlRewrite::handleUrlRewrite($product_attribute, "created");
        // }
    }

    public function deleted(ProductAttribute $product_attribute)
    {
        // if($product_attribute->attribute->name === 'Slug') UrlRewrite::handleUrlRewrite($product_attribute, __FUNCTION__);
    }
}
