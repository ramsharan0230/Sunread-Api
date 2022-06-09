<?php

namespace Modules\Page\Repositories;

use Exception;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Entities\Store;
use Modules\Core\Repositories\BaseRepository;
use Modules\Page\Entities\Page;
use Illuminate\Validation\ValidationException;
use Modules\Page\Entities\PageScope;

class PageRepository extends BaseRepository
{
    protected $pageAttributeRepository, $parent = [], $store_model, $pageScope;

    public function __construct(Page $page, PageAttributeRepository $pageAttributeRepository, Store $store, PageScope $pageScope)
    {
        $this->model = $page;
        $this->model_key = "page";
        $this->rules = [
            "title" => "required",
            "position" => "sometimes|numeric",
            "status" => "sometimes|boolean",
            "meta_title" => "sometimes|nullable",
            "meta_description" => "sometimes|nullable",
            "meta_keywords" => "sometimes|nullable",
            "website_id" => "required|exists:websites,id",
            "layout_type" => "required|in:" . implode(",", Page::$layout_types),
            "stores" => "required|array",
            "components" => "required|array",
        ];
        $this->pageAttributeRepository = $pageAttributeRepository;
        $this->store_model = $store;
        $this->pageScope = $pageScope;
    }

    public function validateSlug(array $data, ?int $id=null): void
    {
        $model = ($id) ? $this->model->where('id', '!=', $id) : $this->model;
        array_map(function($scope) use ($data, $model) {
            $exist_slug = $model->whereSlug($data["slug"])->whereHas("page_scopes", function ($query) use ($scope) {
                $query->whereScope("store")->whereScopeId($scope);
            })->first();
            if ($exist_slug) throw ValidationException::withMessages(["slug" => "Slug has already taken."]);
        }, $data["stores"]);
    }

    public function show(int $id): array
    {
        try
        {
            $attributes = [];
            $data = $this->fetch($id, [ "page_scopes", "page_attributes", "website" ]);
            $components = $data->page_attributes()->orderBy("position", "asc")->get();
            $stores = $data->page_scopes()->pluck("scope_id")->toArray();

            foreach($components as $component)
            {
                $attributes[] = $this->getParent($component);
            }

            $item = $data->toArray();
            unset($item["page_attributes"], $item["page_scopes"]);
            $item["stores"] = $stores;
            $item["components"] = $attributes;
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $item;
    }

    public function getParent(object $component): array
    {
        try
        {
            $this->parent = [];

            $data = collect($this->pageAttributeRepository->config_fields)->where("slug", $component->attribute)->first();
            $this->getChildren($data["mainGroups"], values:$component->value);
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return [
            "id" => $component->id,
            "title" => $data["title"],
            "slug" => $data["slug"],
            "preview" => Storage::url("images/components/{$data["preview"]}"),
            "mainGroups" => $this->parent
        ];
    }

    private function getChildren(array $elements, ?string $key = null, array $values, ?string $slug_key = null): void
    {
        try
        {
            foreach ($elements as $i => &$element) {
                $append_key = isset($key) ? "{$key}.{$i}" : $i;
                $append_slug_key = isset($slug_key) ? "{$slug_key}.{$element['slug']}" : $element["slug"];

                if (isset($element["groups"])) {
                    setDotToArray($append_key, $this->parent,  $element);
                    $this->getChildren($element["groups"], "{$append_key}.groups", $values);
                    continue;
                }

                if (isset($element["subGroups"])) {
                    setDotToArray($append_key, $this->parent,  $element);
                    $this->getChildren($element["subGroups"], "{$append_key}.subGroups", $values);
                    continue;
                }
                
                if ($element["hasChildren"] == 0) {
                    //if ( $element["provider"] !== "" ) $element["options"] = $this->pageAttributeRepository->cacheQuery($element);
                    unset($element["pluck"], $element["provider"], $element["rules"]);

                    if (count($values) > 0) {
                        $default = decodeJsonNumeric(getDotToArray($append_slug_key, $values));
                        if (isset($default)) {
                            $element["default"] = ($element["type"] == "file") ? Storage::url($default) : $default;
                        }
                    }
                    setDotToArray($append_key, $this->parent, $element);
                    continue;
                }

                if (isset($element["type"])) {
                    unset($element["pluck"], $element["provider"], $element["rules"]);

                    if ($element["type"] == "repeater") {
                        $count = isset($values[$element["slug"]]) ? count($values[$element["slug"]]) : 0;
                        $fake_element = $element;
                        $fake_element["attributes"] = [];
                        for ($j=0; $j<$count; $j++) {
                            if ($j==0) setDotToArray($append_key, $this->parent, $fake_element);
                            $this->getChildren($element["attributes"][0], "{$append_key}.attributes.{$j}", $values, "{$append_slug_key}.{$j}");
                        }
                        continue;
                    }
                    if ($element["type"] == "normal") {
                        setDotToArray($append_key, $this->parent,  $element);
                        $this->getChildren($element["attributes"], "{$append_key}.attributes", $values, $append_slug_key);
                        continue;
                    }
                }

                setDotToArray($append_key, $this->parent,  $element);
                $this->getChildren($element["attributes"], "{$append_key}.attributes", $values);
            }
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }
}
