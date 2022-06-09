<?php

namespace Modules\Core\Repositories\StoreFront;

use Exception;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Website;
use Modules\Core\Facades\CoreCache;
use Modules\Core\Repositories\BaseRepository;
use Modules\Core\Traits\Cacheable;
use Modules\Core\Transformers\StoreFront\ChannelResource;

class ChannelRepository extends BaseRepository
{
    protected $repository;

    use Cacheable;

    public function __construct(Channel $channel)
    {
        $this->model = $channel;
    }

    public function getChannelList(object $request): array
    {
        try
        {
            $website = CoreCache::getWebsite($request->header("hc-host"));
            
            $fetched = $this->storeCache("sf_channel_list", $website->id, function () use ($website) {
                return $this->getChannelWithCache($website);
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }  
        
        return $fetched;
    }

    public function getChannelWithCache(object $website): array
    {
        try
        {
            $fetched = [];
            $channels = $this->model->with(["default_store", "stores"])->whereWebsiteId($website->id)->get();
            foreach ($channels as $channel) {
                $fetched[] = new ChannelResource($channel);   
            }

        }
        catch (Exception $exception)
        {
            throw $exception;
        } 
        
        return $fetched;
    }
}

