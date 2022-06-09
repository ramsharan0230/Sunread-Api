<?php


namespace Modules\Product\Observers;

use Modules\Core\Facades\Audit;
use Modules\Product\Entities\Product;

class ProductObserver
{
    public function created(Product $product)
    {
        Audit::log($product, __FUNCTION__);
    }

    public function updated(Product $product)
    {
        Audit::log($product, __FUNCTION__);
    }

    public function deleted(Product $product)
    {
        Audit::log($product, __FUNCTION__);
    }
}
