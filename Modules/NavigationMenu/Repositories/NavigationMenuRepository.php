<?php

namespace Modules\NavigationMenu\Repositories;

use Exception;
use Illuminate\Support\Str;
use Modules\Core\Repositories\BaseRepository;
use Modules\Core\Services\RedisHelper;
use Modules\NavigationMenu\Entities\NavigationMenu;
use Modules\NavigationMenu\Rules\NavigationMenuLocationRule;
use Modules\NavigationMenu\Transformers\StoreFront\NavigationMenuResource;
use Modules\NavigationMenu\Transformers\StoreFront\NavigationMenuItemResource;

class NavigationMenuRepository extends BaseRepository
{
    // Properties for NavigationMenuRepostiory
    protected $repository, $redis_helper;
    protected bool $without_pagination = true;

    /**
     * NavigationMenuRepostiory Class Constructor
     */
    public function __construct(NavigationMenu $navigation_menu, RedisHelper $redis_helper)
    {
        $this->model = $navigation_menu;
        $this->model_key = "navigationMenu";
        $this->redis_helper = $redis_helper;

        $this->rules = [
            // NavigationMenu validation
            "title" => "required|string|min:2|max:250",
            "website_id" => 'required|integer|exists:websites,id',
            "location" => ["string", new NavigationMenuLocationRule()],
            "status" => "in:0,1"
        ];
    }

    /**
     * Create Navigation Menu with Unique Location
     */
    public function createWithUniqueLocation(array $data): object
    {
        if(isset($data['location'])) {
            $navigation_menu = $this->model->where('location', $data['location']);
            $navigation_menu->each(function ($item){
                $this->update(['location' => null], $item->id);
            });
        }
        $created = $this->create($data);
        return $created;
    }

    /**
     * Update Navigation Menu with Unique Location
     */
    public function updateWithUniqueLocation(array $data, int $id): object
    {
        if(isset($data['location'])) {
            $navigation_menu = $this->model->where('location', $data['location']);
            $navigation_menu->each(function ($item) use($id) {
                if($item->id !== $id) $this->update(['location' => null], $item->id);
            });
        }
        $updated = $this->update($data, $id);
        return $updated;
    }

    /**
     * Check if Slug Exists. If not create it
     */
    public function examineSlug(array $data, ?object $navigation_menu = null): array
    {
        if(!isset($data["slug"])) {
            $slug = Str::slug($data["title"]);
            $original_slug = $slug;
            $count = 1;

            while ($this->checkSlug($slug)) {
                $slug = "{$original_slug}-{$count}";
                $count++;
            }

            $data["slug"] = $slug;
        }
        return $data;
    }

    /**
     * Check if Slug Exists Or Not
     */
    public function checkSlug(string $slug)
    {
        $navigation_menu = $this->model->where('slug', $slug)->first();
        return $navigation_menu;
    }

    /**
     * Fetch Navigation From Redis
     */
    public function fetchItemsFromCache(object $request): object
    {
        try
        {
            $coreCache = $this->getCoreCache($request);
            $website = $coreCache->website;
            $channel = $coreCache->channel;
            $store = $coreCache->store;

            $redis_nav_menu_key = "store_front_nav_menu_website_{$website->hostname}_channel_{$channel->code}_store_{$store->code}";

            // if($this->redis_helper->checkIfRedisKeyExists($redis_nav_menu_key)) {
                // $fetched = collect($this->redis_helper->getRedisData($redis_nav_menu_key));
            // } else {
                $fetched = $this->fetchWithItems($request, callback:function() use($website){
                    return $this->model()->where('status', 1)->whereNotNull('location')->where('website_id', $website->id);
                });
                // $this->redis_helper->storeCache($redis_nav_menu_key, $fetched);
            // }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $fetched;

    }

    /**
     * Fetch Navigation Menu with Items
     */
    public function fetchWithItems(object $request, array $with = [], ?callable $callback = null): object
    {
        try
        {
            $navigation_menus = $this->fetchAll($request, $with, $callback);
            $coreCache = $this->getCoreCache($request);
            $channel = $coreCache->channel;
            $store = $coreCache->channel;

            foreach($navigation_menus as $nav_menu)
            {
                $items = $nav_menu->rootNavigationMenuItems;
                $nav_menu->items = $this->fetchNavigationMenuItems($items, $store, $channel);
            }
            $data = NavigationMenuResource::collection($navigation_menus);

        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $data;

    }


    /**
     * Get Navigation Menu Items
     */
    private function fetchNavigationMenuItems($navigationMenuItems)
    {
        try
        {
            $navigation_menu_items_resource = NavigationMenuItemResource::collection($navigationMenuItems);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $navigation_menu_items_resource;
    }

}

