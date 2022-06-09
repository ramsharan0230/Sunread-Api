<?php

namespace Modules\Core\Listeners;

use Exception;
use Illuminate\Support\Facades\Redis;
use Modules\Core\Entities\Store;
use Modules\Core\Jobs\CoreCacheJob;
use Modules\Product\Jobs\ReindexMigrator;
use Modules\Product\Jobs\RemoveIndex;

class StoreListener
{
    public function create(object $store): void
    {
        if ($store->status == 1) {
            CoreCacheJob::dispatch( "createStoreCache", $store )->onQueue("high");

            //indexing products in elasticsearch for new store
            ReindexMigrator::dispatch($store)->onQueue("index");
        }
        $this->delChannelListCache();
    }

    public function beforeUpdate(int $store_id): void
    {
        $store = Store::findOrFail($store_id);
        CoreCacheJob::dispatch( "deleteStoreCache", collect($store) )->onQueue("high");
    }

    public function update(object $store): void
    {
        if ($store->status == 1) {
            CoreCacheJob::dispatch( "createStoreCache", $store )->onQueue("high");
        } else {
            CoreCacheJob::dispatch( "deleteStoreCache", $store)->onQueue("high");
        }

        $this->delChannelListCache();
    }

    public function delete(object $store): void
    {
        CoreCacheJob::dispatch( "deleteStoreCache", collect($store) )->onQueue("high");
        $this->delChannelListCache();

        //remove index
        RemoveIndex::dispatch(collect($store))->onQueue("index");
    }

    public function delChannelListCache(): void
    {
        try
        {
            if (count(Redis::keys("sf_channel_list*")) > 0) {
                Redis::del(Redis::keys("sf_channel_list*"));
            } 
        }
        catch(Exception $exception )
        {
            throw $exception;
        }
    }
}
