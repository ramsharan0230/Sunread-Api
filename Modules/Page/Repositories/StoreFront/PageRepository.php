<?php

namespace Modules\Page\Repositories\StoreFront;

use Exception;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Entities\Store;
use Modules\Core\Repositories\BaseRepository;
use Modules\Page\Entities\Page;
use Modules\Core\Entities\Website;
use Modules\Core\Facades\CoreCache;
use Modules\Page\Entities\PageAttribute;
use Modules\Product\Transformers\StoreFront\ProductResource;

class PageRepository extends BaseRepository
{
    public $config_fields, $parent = [];

    public function __construct()
    {
        $this->model = new Page();
        $this->model_key = "page";
        $this->without_pagination = true;
        $this->config_fields = config("attributes");
    }

    public function findPage(object $request, string $slug): array
    {
        try
        {
            $fetched = [];
            $coreCache = $this->getCoreCache($request);

            $page = $this->model->whereWebsiteId($coreCache->website->id)->whereStatus(1)->whereSlug($slug)->firstOrFail();
            $page_scope = $page->page_scopes()->whereScope("store");
            $all_scope = (clone $page_scope)->whereScopeId(0)->first();
            if (!$all_scope) $page_scope->whereScopeId($coreCache->store->id)->firstOrFail();
            $fetched = $page->toArray();

            $page_componnets = PageAttribute::wherePageId($page->id)->orderBy("position", "asc")->get();
            $fetched["components"] = $this->getComponent($coreCache, $page_componnets);
            unset($fetched["page_attributes"], $fetched["created_at"], $fetched["updated_at"]);
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getComponent(object $coreCache, object $components): array
    {
        try
        {
            $all_components = [];
            foreach($components as $component)
            {
                $data["component"] = $component->attribute;
                $this->getElements($coreCache, $component->attribute, $component->value);
                $data["attributes"] = $this->parent;
                $all_components[] = $data;
            }
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $all_components;
    }

    public function getElements(object $coreCache, string $component, array $attributes)
    {
        try
        {
            $this->parent = [];
            $group_elements = collect($this->config_fields)->where("slug", $component)->pluck("mainGroups")->first();
            $this->getChildren($group_elements, $coreCache, values:$attributes);
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    private function getChildren(array $elements, object $coreCache, ?string $key = null, array $values, ?string $slug_key = null): void
    {
        try
        {
            foreach($elements as $i => &$element)
            {
                $append_key = isset($key) ? "{$key}.{$element['slug']}" : $element["slug"];
                $append_slug_key = isset($slug_key) ? "{$slug_key}.{$element['slug']}" : $element["slug"];

                if(isset($element["groups"])) {
                    setDotToArray($append_key, $this->parent,  []);
                    $this->getChildren($element["groups"], $coreCache, $append_key, $values);
                    continue;
                }

                if(isset($element["subGroups"])) {
                    setDotToArray($append_key, $this->parent,  []);
                    $this->getChildren($element["subGroups"], $coreCache, $append_key, $values);
                    continue;
                }

                if ($element["hasChildren"] == 0) {

                    //skip sibling of element product
                    if(isset($product_null_state) && $product_null_state == $key) continue;

                    if (count($values) > 0) {
                        $default = decodeJsonNumeric(getDotToArray($append_slug_key, $values));

                        if ($element["slug"] == "view_more_link") $default = $this->getDynamicLink($default, $values, $coreCache);

                        if ($element["provider"] != "") {
                            $default = $this->getProviderData($coreCache->store, $element, $default);

                            //skip element product itself if it is found to be null
                            if($element["slug"] == "product" && !$default) {
                                $product_null_state = $key;
                                continue;
                            }
                        }

                        if($default && ($element["type"] == "file")) $default = Storage::url($default);
                    }

                    setDotToArray($append_key, $this->parent, $default);
                    continue;
                }

                if (isset($element["type"])) {

                    if ($element["type"] == "repeater") {
                        $count = isset($values[$element["slug"]]) ? count($values[$element["slug"]]) : 0;
                        for($j=0; $j<$count; $j++)
                        {
                            if ($j==0) setDotToArray($append_key, $this->parent, []);
                            $this->getChildren($element["attributes"][0], $coreCache, "{$append_key}.{$j}", $values, "{$append_slug_key}.{$j}");
                        }
                        $fake_product_values = getDotToArray($append_key, $this->parent);
                        setDotToArray($append_key, $this->parent, collect($fake_product_values)->values()->toArray());
                        continue;
                    }
                    if ($element["type"] == "normal") {
                        setDotToArray($append_key, $this->parent,  []);
                        $this->getChildren($element["attributes"], $coreCache, $append_key, $values, $append_slug_key);
                        continue;
                    }
                }

                setDotToArray($append_key, $this->parent,  []);
                $this->getChildren($element["attributes"], $coreCache, "{$append_key}", $values);
            }
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function getProviderData(object $store, array $element, mixed $values): mixed
    {
        try
        {
            $model = new $element["provider"];
            $pluck = $element["pluck"][1];
            $fetched = $model->where($pluck, $values)->first();

            if($fetched && $element["slug"] == "product") {
                $is_visibility = $fetched->value([
                    "scope" => "store",
                    "scope_id" => $store->id,
                    "attribute_slug" => "visibility"
                ]);

                $fetched = ($is_visibility?->code != "not_visible") ? new ProductResource($fetched) : null;
            }
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getDynamicLink(mixed $default_url, mixed $values, object $coreCache): mixed
    {
        try
        {
            $dynamic_bool = getDotToArray("dynamic_link", $values);
            if($dynamic_bool == "1") $default_url = "{$coreCache->channel->code}/{$coreCache->store->code}/{$default_url}";
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $default_url;
    }

}
