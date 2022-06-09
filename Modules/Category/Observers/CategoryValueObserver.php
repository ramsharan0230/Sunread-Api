<?php


namespace Modules\Category\Observers;

use Exception;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Modules\UrlRewrite\Facades\UrlRewrite;
use Modules\Category\Entities\CategoryValue;
use Modules\Core\Entities\Website;
use Modules\Product\Jobs\SingleIndexing;
use Modules\Product\Traits\ElasticSearch\PrepareIndex;

class CategoryValueObserver
{
    use PrepareIndex;

    public function created(CategoryValue $category_value)
    {
        if ($category_value->attribute == "slug") {
            $this->delCategoryCache();
        }
    }

    public function updated(CategoryValue $category_value)
    {
        if ($category_value->attribute == "slug") {
            $this->delCategoryCache();
        }
    }

    public function deleted(CategoryValue $category_value)
    {
    
    }

    public function deleting(CategoryValue $category_value)
    {
        if ($category_value->attribute == "slug") {
            $this->delCategoryCache();
        }
    }

    public function delCategoryCache(): void
    {
        try
        {
            if (count(Redis::keys("category*")) > 0) {
                Redis::del(Redis::keys("category*"));
            } 
        }
        catch(Exception $exception )
        {
            throw $exception;
        }
    }
}
