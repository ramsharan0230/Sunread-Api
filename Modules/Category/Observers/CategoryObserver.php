<?php


namespace Modules\Category\Observers;

use Exception;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Modules\Core\Facades\Audit;
use Modules\Category\Entities\Category;
use Modules\UrlRewrite\Facades\UrlRewrite;
use Illuminate\Support\Str;
use Modules\Core\Entities\Website;
use Modules\Product\Jobs\CategoryWiseReindexMigrator;
use Modules\Product\Jobs\SingleIndexing;
use Modules\Product\Traits\ElasticSearch\PrepareIndex;

class CategoryObserver
{
    use PrepareIndex;

    public function created(Category $category)
    {
        Audit::log($category, __FUNCTION__);
        $this->delCategoryCache();
        //UrlRewrite::handleUrlRewrite($category, __FUNCTION__);
    }

    public function updated(Category $category)
    {
        Audit::log($category, __FUNCTION__);
        $this->delCategoryCache();
        //UrlRewrite::handleUrlRewrite($category, __FUNCTION__);
    }

    public function deleted(Category $category)
    {
        Audit::log($category, __FUNCTION__);
        $this->delCategoryCache();
        //UrlRewrite::handleUrlRewrite($category, __FUNCTION__);
    }

    public function deleting(Category $category)
    {
        CategoryWiseReindexMigrator::dispatch($category->products)->onQueue("index");;
        Audit::log($category, __FUNCTION__);
        //UrlRewrite::handleUrlRewrite($category, __FUNCTION__);
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
