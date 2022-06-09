<?php

namespace Modules\Core\Listeners;

use Modules\Core\Entities\Website;
use Modules\Core\Jobs\CoreCacheJob;

class WebsiteListener
{
    public function create(object $website): void
    {
        if ($website->status == 1) {
            CoreCacheJob::dispatch( "createWebsiteCache", $website )->onQueue("high");
        }
    }

    public function beforeUpdate(int $website_id): void
    {
        $website = Website::findOrFail($website_id);
        CoreCacheJob::dispatch( "updateBeforeWebsiteCache", collect($website) )->onQueue("high");
    }

    public function update(object $website): void
    {
        if ($website->status == 1) {
            CoreCacheJob::dispatch( "updateWebsiteCache", $website )->onQueue("high");
        } else {
            CoreCacheJob::dispatch( "deleteWebsiteCache", $website)->onQueue("high"); 
        }
    }

    public function beforeDelete(int $website_id): void
    {
        $website = Website::findOrFail($website_id);
        CoreCacheJob::dispatch( "deleteBeforeWebsiteCache", collect($website) )->onQueue("high");
    }

    public function delete(object $website): void
    {
        CoreCacheJob::dispatch( "deleteWebsiteCache", collect($website) )->onQueue("high");
    }
}
