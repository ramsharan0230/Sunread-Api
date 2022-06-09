<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;
use Modules\Core\Exceptions\PageNotFoundException;
use Modules\Core\Facades\CoreCache;
use Modules\Core\Facades\SiteConfig;
use Modules\Core\Transformers\StoreFront\StoreResource;
use Modules\Page\Entities\Page;

class ResolverHelper {

    protected $website;

    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    public function getDomain(?string $naked_domain = null): string
    {
        $naked_domain = str_replace(["http://", "https://"], "", $naked_domain ?? Request::root());
        $naked_domain = explode("/", $naked_domain)[0];
        $root_domain = explode(":", $naked_domain)[0];

        return $root_domain;
    }

    public function fetch(object $request, ?callable $callback = null): array
    {
        try
        {
            $websiteData = [];

            $fallback_id = config("website.fallback_id");
            if ($request->hasHeader('hc-host')) $website = CoreCache::getWebsite($request->header("hc-host"));
            else $website = Website::whereId($fallback_id)->firstOrFail();
            $websiteData = collect($website)->only(["id","name","code", "hostname"])->toArray();

            $channel = $this->getChannel($request, $website);
            $websiteData["channel"] = collect($channel)->only(["id","name","code"])->toArray();
            $websiteData["channel"]["icon"] = SiteConfig::fetch("channel_icon", "channel", $channel->id);

            $all_stores = collect(CoreCache::getChannelAllStore($website, $channel))->map(function ($store) {
                return new StoreResource(json_decode($store));
            });

            $store = $this->getStore($request, $website, $channel);
            $storeData = collect($store)->only(["id","name","code"])->toArray();

            $storeData["locale"] = SiteConfig::fetch("store_locale", "store", $store->id)?->code;
            $storeData["icon"] = SiteConfig::fetch("store_icon", "store", $store->id);

            $websiteData["channel"]["store"] = $storeData;

            $websiteData["stores"] = $all_stores;

            $websiteData["pages"] = $this->getPages($website);

            $websiteData["meta"] = [
                "logo" => SiteConfig::fetch("logo", "channel", $channel->id),
                "footer_logo" => SiteConfig::fetch("footer_logo", "channel", $channel->id),
            ];

            if ($callback) $website = $callback($websiteData);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $websiteData;
    }

    public function getChannel(object $request, object $website): object
    {
        try
        {
            $channel_code = $request->header("hc-channel");
            if($channel_code) { 
                $channel = CoreCache::getChannel($website, $channel_code);
            }
            else {
                $channel = $this->checkCondition("website_default_channel", $website);  
                if(!$channel) {
                    $cache = CoreCache::getWebsiteAllChannel($website);
                    if(!$cache) throw new PageNotFoundException(__("core::app.response.not-found", ["name" => "Channel"]));
                    $channel = json_decode($cache[0]);
                }
            }    
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $channel;
    }

    public function getStore(object $request, object $website, object $channel): object
    {
        try
        {
            $store_code = $request->header("hc-store");

            if($store_code) $store = CoreCache::getStore($website, $channel, $store_code);
            else {
                $store = Store::whereStatus(1)->find($channel->default_store_id);
                if(!$store) {
                    $cache = CoreCache::getChannelAllStore($website, $channel);
                    if(!$cache) throw new PageNotFoundException(__("core::app.response.not-found", ["name" => "Store"]));
                    $store = json_decode($cache[0]);
                }
            }
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $store;
    }

    public function getPages(object $website): ?array
    {
        try
        {
            $pages = Page::whereWebsiteId($website->id)->whereStatus(1)->get();
            $data = [];
            foreach ( $pages as $page ) $data[$page->slug] = [ "id" => $page->id, "code" => $page->slug, "title" => $page->title ] ;
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $data;
    }

    public function checkCondition(string $slug, object $website): ?object
    {
        return SiteConfig::fetch($slug, "website", $website->id);
    }
}