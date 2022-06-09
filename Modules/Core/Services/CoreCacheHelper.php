<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Redis;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;
use Exception;

class CoreCacheHelper
{
    protected $website, $channel, $store, $old_website_hostname, $old_channel_code;

    public function __construct(Website $website, Channel $channel, Store $store)
    {
        $this->website = $website;
        $this->channel = $channel;
        $this->store = $store;
    }

    public function createWebsiteCache(object $website): void
    {
        try
        {
            unset($website->channels, $website->stores);
            Redis::set("sf_website_{$website->hostname}", $website);
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function updateBeforeWebsiteCache(object $website): void
    {
        try
        {
            $this->old_website_hostname = $website["hostname"];
            Redis::del("sf_website_{$website["hostname"]}");
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function updateWebsiteCache(object $website): void
    {
        try
        {
            unset($website->channels, $website->stores);
            if( $this->old_website_hostname != $website->hostname ) {
                if( count(Redis::keys("sf_c_website_{$this->old_website_hostname}_*")) > 0 ) Redis::del(Redis::keys("sf_c_website_{$this->old_website_hostname}_*"));
                if( count(Redis::keys("sf_s_website_{$this->old_website_hostname}_*")) > 0) Redis::del(Redis::keys("sf_s_website_{$this->old_website_hostname}_*"));
            }

            Redis::set("sf_website_{$website->hostname}", $website);
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function deleteBeforeWebsiteCache(object $website): void
    {
        try
        {
            $channels = $this->channel->whereWebsiteId($website["id"])->get();
            if($channels) {
                foreach ($channels as $channel) {
                    Redis::del("sf_channel_{$channel->code}");

                    $stores = $this->store->whereStoreId($channel->id)->get();
                    if ($stores) foreach ($stores as $store) Redis::del("sf_store_{$store->code}");
                }
            }
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function deleteWebsiteCache(object $website): void
    {
        try
        {
            Redis::del("sf_website_{$website["hostname"]}");
            if( count(Redis::keys("sf_c_website_{$website["hostname"]}_*")) > 0 ) Redis::del(Redis::keys("sf_c_website_{$website["hostname"]}_*"));
            if( count(Redis::keys("sf_s_website_{$website["hostname"]}_*")) > 0) Redis::del(Redis::keys("sf_s_website_{$website["hostname"]}_*"));
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function createChannelCache(object $channel): void
    {
        try
        {
            $website_hostname = $channel->website->hostname;
            unset($channel->website, $channel->stores);
            Redis::set("sf_c_website_{$website_hostname}_channel_{$channel->code}", $channel);
            Redis::set("sf_channel_{$channel->code}", $channel);
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function updateBeforeChannelCache(object $channel): void
    {
        try
        {
            $website = $this->website->whereStatus(1)->findOrFail($channel["website_id"]);
            $this->old_channel_code = $channel["code"];

            Redis::del("sf_c_website_{$website->hostname}_channel_{$channel["code"]}");
            Redis::del("sf_channel_{$channel["code"]}");
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function updateChannelCache(object $channel): void
    {
        try
        {
            $website_hostname = $channel->website->hostname;

            if( $this->old_channel_code != $channel->code ) {
                if( count(Redis::keys("sf_s_website_{$channel->website->hostname}_channel_{$this->old_channel_code}_store_*")) > 0 ) Redis::del(Redis::keys("sf_s_website_{$channel->website->hostname}_channel_{$this->old_channel_code}_store_*"));
            }
            unset($channel->website, $channel->stores);
            Redis::set("sf_c_website_{$website_hostname}_channel_{$channel->code}", $channel);
            Redis::set("sf_channel_{$channel->code}", $channel);
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function deleteBeforeChannelCache(object $channel): void
    {
        try
        {
            $stores = $this->store->whereChannelId($channel["id"])->get();
            foreach ($stores as $store) Redis::del("sf_store_{$store->code}");
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function deleteChannelCache(object $channel): void
    {
        try
        {
            $website = $this->website->findOrFail($channel["website_id"]);

            Redis::del("sf_c_website_{$website->hostname}_channel_{$channel["code"]}");
            if( count(Redis::keys("sf_s_website_{$website->hostname}_channel_{$channel["code"]}_*")) > 0 ) Redis::del(Redis::keys("sf_s_website_{$website->hostname}_channel_{$channel["code"]}_*"));
            Redis::del("sf_channel_{$channel["code"]}");
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function createStoreCache(object $store): void
    {
        try
        {
            $channel = $store->channel;
            $store["website_id"] = $channel->website->id;

            unset($store->channel, $store->website);
            Redis::set("sf_s_website_{$channel->website->hostname}_channel_{$channel->code}_store_{$store->code}", $store);
            Redis::set("sf_store_{$store->code}", $store);
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function deleteStoreCache(object $store): void
    {
        try
        {
            $channel = $this->channel->findOrFail($store["channel_id"]);
            Redis::del("sf_s_website_{$channel->website->hostname}_channel_{$channel->code}_store_{$store["code"]}");
            Redis::del("sf_store_{$store["code"]}");
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function getWebsite(string $website_hostname): ?object
    {
        try
        {
            $website = json_decode(Redis::get("sf_website_{$website_hostname}"));
            if( ! $website ) {
                $website = $this->website->whereHostname($website_hostname)->whereStatus(1)->setEagerLoads([])->firstOrFail();
                Redis::set("sf_website_{$website_hostname}", $website);
            }

            return $website;
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function getChannel(object $website, string $channel_code): ?object
    {
        try
        {
            $channel = json_decode(Redis::get("sf_c_website_{$website->hostname}_channel_{$channel_code}"));
            if( !$channel ) {
                $channel = $this->channel->whereWebsiteId($website->id)->whereCode($channel_code)->whereStatus(1)->setEagerLoads([])->firstOrFail();
                unset($channel->stores, $channel->website);

                Redis::set("sf_c_website_{$website->hostname}_channel_{$channel_code}", $channel);
                Redis::SETNX("sf_channel_{$channel_code}", $channel);
            }

            return $channel;
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function getStore(object $website, object $channel, string $store_code): ?object
    {
        try
        {
            $store = json_decode(Redis::get("sf_s_website_{$website->hostname}_channel_{$channel->code}_store_{$store_code}"));
            if( !$store ) {
                $store = $this->store->whereChannelId($channel->id)->whereCode($store_code)->whereStatus(1)->setEagerLoads([])->firstOrFail();
                $store["website_id"] = $website->id;
                unset($store->channel, $store->website);

                Redis::set("sf_s_website_{$website->hostname}_channel_{$channel->code}_store_{$store_code}", $store);
                Redis::SETNX("sf_store_{$store_code}", $store);
            }

            return $store;
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function getWebsiteAllChannel(object $website): ?array
    {
        try
        {
            $keys = Redis::keys("sf_c_website_{$website->hostname}_*");
            if(!$keys) {
                $channels = $this->channel->whereWebsiteId($website->id)->whereStatus(1)->setEagerLoads([])->get();
                foreach($channels as $channel) {
                    unset($channel->stores, $channel->website);

                    Redis::SETNX("sf_c_website_{$website->hostname}_channel_{$channel->code}", $channel);
                    Redis::SETNX("sf_channel_{$channel->code}", $channel);
                }
                $keys = Redis::keys("sf_c_website_{$website->hostname}_*");
            }

            return $keys ? Redis::mget($keys) : null;
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function getWebsiteAllStore(object $website): ?array
    {
        try
        {
            $keys = Redis::keys("sf_s_website_{$website->hostname}_*");
            if(!$keys) {
                $stores = $this->website->whereStatus(1)->find($website->id)->channels->map(function ($channel) {
                    return $channel->stores;
                })->flatten(1);
                foreach($stores as $store) {
                    $channel = $this->channel->whereStatus(1)->findOrFail($store->channel_id);
                    $store["website_id"] = $website->id;

                    unset($store->channel, $store->website);
                    Redis::SETNX("sf_s_website_{$website->hostname}_channel_{$channel->code}_store_{$store->code}", $store);
                    Redis::SETNX("sf_store_{$store->code}", $store);
                }
                $keys = Redis::keys("sf_s_website_{$website->hostname}_*");
            }

            return $keys ? Redis::mget($keys) : null;
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function getChannelAllStore(object $website, object $channel): ?array
    {
        try
        {
            $keys = Redis::keys("sf_s_website_{$website->hostname}_channel_{$channel->code}_*");

            if(!$keys) {
                $stores = $this->store->whereChannelId($channel->id)->whereStatus(1)->setEagerLoads([])->get();
                foreach($stores as $store) {
                    $store["website_id"] = $website->id;

                    unset($store->channel, $store->website);
                    Redis::SETNX("sf_s_website_{$website->hostname}_channel_{$channel->code}_store_{$store->code}", $store);
                    Redis::SETNX("sf_store_{$store->code}", $store);
                }
                $keys = Redis::keys("sf_s_website_{$website->hostname}_channel_{$channel->code}_*");
            }

            return $keys ? Redis::mget($keys) : null;
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function getChannelWithCode(string $channel_code): ?object
    {
        try
        {
            $channel = json_decode(Redis::get("sf_channel_{$channel_code}"));

            if( !$channel ) {
                $channel = $this->channel->whereCode($channel_code)->whereStatus(1)->setEagerLoads([])->firstOrFail();
                $website = $channel->website;
                unset($channel->stores, $channel->website);

                Redis::set("sf_c_website_{$website->hostname}_channel_{$channel_code}", $channel);
                Redis::SETNX("sf_channel_{$channel_code}", $channel);
            }

            return $channel;
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function getStoreWithCode(string $store_code): ?object
    {
        try
        {
            $store = json_decode(Redis::get("sf_store_{$store_code}"));

            if( !$store ) {
                $store = $this->store->whereCode($store_code)->whereStatus(1)->setEagerLoads([])->firstOrFail();
                $channel = $store->channel;
                $website = $channel->website;
                unset($store->channel, $store->website);
                Redis::set("sf_c_website_{$website->hostname}_channel_{$channel->code}_store_{$store_code}", $store);
                Redis::SETNX("sf_store_{$store_code}", $store);
            }

            return $store;
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }
}
