<?php

namespace Modules\Category\Traits;

use Illuminate\Support\Facades\Storage;
use Modules\Category\Entities\Category;
use Modules\Category\Entities\CategoryValue;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;

trait HasScope
{
    public $channel_model, $store_model, $value_model;

    public function createModel(): void
    {
        $this->channel_model = new Channel();
        $this->store_model = new Store();
        $this->value_model = new CategoryValue();
    }

    public function getDefaultValues(array $data): ?object
    {
        if($data["scope"] != "website")
        {
            switch($data["scope"])
            {
                case "store":
                    $data["scope"] = "channel";
                    $data["scope_id"] = $this->store_model->find($data["scope_id"])->channel->id;
                    break;

                case "channel":
                    $data["scope"] = "website";
                    $data["scope_id"] = $this->channel_model->find($data["scope_id"])->website->id;
                    break;
            }
            return $this->has($data) ? $this->getValues($data) : $this->getDefaultValues($data);
        }
        return $this->has($data) ? $this->getValues($data) : null;
    }

    public function getValues(array $data): object
    {
        return $this->checkCondition($data)->first();
    }

    public function scopeFilter(string $scope, string $element_scope): bool
    {
        if($scope == "channel" && in_array($element_scope, ["website"])) return true;
        if($scope == "store" && in_array($element_scope, ["website", "channel"])) return true;
        return false;
    }

    public function has(array $data)
    {
        return (boolean) $this->checkCondition($data)->count();
    }

    public function checkCondition(array $data): object
    {
        return $this->value_model->whereCategoryId($data["category_id"])->whereScope($data["scope"])->whereScopeId($data["scope_id"])->whereAttribute($data["attribute"]);
    }

    public function checkSlug(array $data, ?string $slug, ?object $category = null): ?object
    {
        $parent_id = isset($data["parent_id"]) ? $data["parent_id"] : $category?->parent_id;
        $website_id = isset($data["website_id"]) ? $data["website_id"] : $category?->website_id;

        return Category::whereParentId($parent_id)->whereWebsiteId($website_id)->whereHas("values", function ($query) use ($slug, $category) {
            if($category) $query = $query->where('category_id', '!=', $category->id);
            $query->whereAttribute("slug")->whereValue($slug);
        })->first();
    }

    //this method is for all the attribute except attribute of file type.
    public function value(array $data, string $attribute): mixed
    {
        $this->createModel();
        $data = array_merge($data, [ "attribute" => $attribute], ["category_id" => $this->id]);
        $default = $this->has($data) ? $this->getValues($data) : $this->getDefaultValues($data);
        return $default?->value;
    }

    //this method is for all the attribute including file type.
    public function getCategoryVal(array $data, string $attribute): mixed
    {
        $this->createModel();
        $elements = collect(config("category.attributes"))->pluck("elements")->flatten(1);
        $attribute_data = $elements->where("slug", $attribute)->first();
        $data = array_merge($data, [ "attribute" => $attribute], ["category_id" => $this->id]);
        $default = $this->has($data) ? $this->getValues($data) : $this->getDefaultValues($data);
        return ($attribute_data["type"] == "file" && $default?->value) ? Storage::url($default?->value) : $default?->value;
    }
}
