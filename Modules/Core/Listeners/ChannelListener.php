<?php

namespace Modules\Core\Listeners;

use Exception;
use Illuminate\Support\Facades\Redis;
use Modules\Core\Entities\Channel;
use Modules\Core\Jobs\CoreCacheJob;

class ChannelListener
{
    public function create(object $channel): void
    {
        if ($channel->status == 1) {
            CoreCacheJob::dispatch( "createChannelCache", $channel )->onQueue("high");
        }
        $this->delChannelListCache();
    }

    public function beforeUpdate(int $channel_id): void
    {
        $channel = Channel::findOrFail($channel_id);
        CoreCacheJob::dispatch( "updateBeforeChannelCache", collect($channel) )->onQueue("high");
    }

    public function update(object $channel): void
    {
        if ($channel->status == 1) {
            CoreCacheJob::dispatch( "updateChannelCache", $channel )->onQueue("high");
        } else {
            CoreCacheJob::dispatch( "deleteChannelCache", $channel)->onQueue("high"); 
        }

        $this->delChannelListCache();
    }

    public function beforeDelete(int $channel_id): void
    {
        $channel = Channel::findOrFail($channel_id);
        CoreCacheJob::dispatch( "deleteBeforeChannelCache", collect($channel) )->onQueue("high");
    }

    public function delete(object $channel): void
    {
        CoreCacheJob::dispatch( "deleteChannelCache", collect($channel) )->onQueue("high");
        $this->delChannelListCache();
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
