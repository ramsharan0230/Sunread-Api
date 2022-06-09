<?php

namespace Modules\Product\Repositories\StoreFront;

use Exception;
use Illuminate\Support\Facades\Storage;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeSet;
use Modules\Category\Entities\Category;
use Modules\Category\Entities\CategoryValue;
use Modules\Category\Exceptions\CategoryNotFoundException;
use Modules\Category\Repositories\StoreFront\CategoryRepository;
use Modules\Core\Repositories\BaseRepository;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductAttribute;
use Modules\Product\Exceptions\ProductNotFoundIndividuallyException;
use Modules\Product\Repositories\ProductSearchRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Services\Pipe;
use Modules\Page\Repositories\StoreFront\PageRepository;
use Modules\Product\Repositories\StoreFront\ProductFormatRepository as StoreFrontProductFormatRepository;
use Illuminate\Support\Facades\Log;
use Modules\Core\Traits\Cacheable;

class ProductRepository extends BaseRepository
{
    use Cacheable;

    protected $search_repository;
    protected $categoryRepository;
    protected $pageRepository;
    protected $product_format_repo;
    protected int $count;
    protected array $nested_product = [];
    protected array $config_products = [];
    protected array $final_product_val = [];

    public function __construct()
    {
        $this->model = new Product();
        $this->model_key = "catalog.products";
        $this->search_repository = new ProductSearchRepository();
        $this->categoryRepository = new CategoryRepository();
        $this->model_name = "Product";
        $this->pageRepository = new PageRepository();
        $this->product_format_repo = new StoreFrontProductFormatRepository();
        $this->count = 0;
    }

    public function show(object $request, mixed $identifier, ?int $parent_identifier = null): ?array
    {
        try
        {
            $coreCache = $this->getCoreCache($request);

            $relations = [
                "catalog_inventories",
                "images",
                "images.types",
                "categories",
                "categories.values",
                "product_attributes",
                "product_attributes.attribute",
                "attribute_configurable_products",
                "attribute_configurable_products.attribute",
                "variants",
                "parent.variants.attribute_options_child_products.attribute_option",
                "parent.variants.product_attributes",
                "variants.attribute_configurable_products",
                "variants.attribute_configurable_products.attribute",
                "variants.attribute_configurable_products.attribute_option",
                "attribute_options_child_products"

            ];
            if (!$parent_identifier) {
                $product_attr = ProductAttribute::query()->with(["value"]);
                $attribute_id = Attribute::whereSlug("url_key")->first()?->id;
                $product_attr = $product_attr->whereAttributeId($attribute_id)->get()->filter( fn ($attr_product) => $attr_product->value->value == $identifier)->first();

                if (isset($product_attr->scope)) {
                    if (in_array($product_attr->scope, ["channel", "website"])) {
                        $this->checkScopeForUrlKey($product_attr?->product_id, $attribute_id, $coreCache, $product_attr?->scope);
                    }
                    if ($product_attr->scope == "store" && $product_attr?->scope_id != $coreCache->store->id) {
                        throw new ProductNotFoundIndividuallyException();
                    }
                }

                $product = Product::whereId($identifier)
                    ->orWhere("id", $product_attr?->product_id)
                    ->whereWebsiteId($coreCache->website->id)
                    ->whereStatus(1)
                    ->with($relations)
                    ->firstOrFail();
            } else {
                $product = Product::whereId($identifier)
                    ->whereParentId($parent_identifier)
                    ->whereWebsiteId($coreCache->website->id)
                    ->whereStatus(1)
                    ->with($relations)
                    ->firstOrFail();
            }

            $channel_status = $product->channels()->whereChannelId($coreCache->store?->channel_id)->first();
            if ($channel_status) {
                throw new ProductNotFoundIndividuallyException();
            }

            $cache_name = "product_details_{$product->id}_{$coreCache->channel->id}_{$coreCache->store->id}";

            $product_details = json_decode(Redis::get($cache_name));

            if (!$product_details) {
                $product_details = $this->productDetail($request, $product, $coreCache, $parent_identifier);

                if ( $product->type == "configurable" || ($product->type == "simple" && isset($product->parent_id))) {
                    $product_details = $this->getConfigurableData($product, $coreCache, $product_details);
                }

                Redis::set($cache_name, json_encode($product_details));
            } else  {
                $product_details = collect($product_details)->toArray();
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $product_details;
    }

    public function checkScopeForUrlKey(
        ?int $product_id,
        int $attribute_id,
        object $coreCache,
        ?string $custom_scope
    ): void {
        try
        {
            if ($custom_scope == "channel") {
                $scope_product_attr = ProductAttribute::whereAttributeId($attribute_id)
                    ->whereProductId($product_id)
                    ->whereScope("store")
                    ->whereScopeId($coreCache->store->id)
                    ->first();
                if ($scope_product_attr) {
                    throw new ProductNotFoundIndividuallyException();
                }
            }
            if ($custom_scope == "website") {
                $scope_product_attr = ProductAttribute::whereAttributeId($attribute_id)
                    ->whereProductId($product_id)
                    ->whereScope("channel")
                    ->whereScopeId($coreCache->channel->id)
                    ->first();
                if ($scope_product_attr) {
                    throw new ProductNotFoundIndividuallyException();
                } else {
                    $this->checkScopeForUrlKey($product_id, $attribute_id, $coreCache, "channel");
                }
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function productDetail(
        object $request,
        object $product,
        object $coreCache,
        ?int $parent_identifier = null
    ): ?array {
        try
        {
            $mainAttribute = [ "name", "sku", "type", "url_key", "quantity", "visibility", "price", "special_price", "special_from_date", "special_to_date", "short_description", "meta_title", "meta_keywords", "meta_description", "new_from_date", "new_to_date", "animated_image", "disable_animation"];
            $store = $coreCache->store;

            $attribute_set = AttributeSet::where("id", $product->attribute_set_id)->first();
            $attributes = $attribute_set->attribute_groups->map(function($attributeGroup){
                return $attributeGroup->attributes;
            })->flatten(1);

            $technical_details = $attribute_set->attribute_groups()
                ->whereName("Technical Details")
                ->first()
                ->attributes()
                ->pluck("slug")
                ->toArray();
            $technical_details = array_merge($technical_details, ["description"]);
            $data = [];

            foreach ($attributes as $attribute) {
                if (in_array($attribute->slug, ["sku", "status", "quantity_and_stock_status", "has_weight", "weight", "cost", "category_ids"])) {
                    continue;
                }

                $match = [
                    "scope" => "store",
                    "scope_id" => $store->id,
                    "attribute_id" => $attribute->id
                ];
                $values = $product->value($match);

                if (!$parent_identifier && $attribute->slug == "visibility" && $values?->code == "not_visible") {
                    throw new ProductNotFoundIndividuallyException();
                }

                if ($attribute->slug == "gallery") {
                    $data = $this->getProductImages($product, $data);
                    continue;
                }

                if ($attribute->slug == "component") {
                    $product_builders = $this->getProductComponents($product, $store, $match);
                    $data["product_builder"] = $product_builders ? $this->pageRepository->getComponent($coreCache, $product_builders) : [];
                    continue;
                }

                if ($product->type == "configurable" && ($attribute->slug == "price" ||  $attribute->slug == "special_price")) {
                    $first_variant = $product->variants->first();
                    $data[$attribute->slug] = $first_variant->value($match);
                    continue;
                }

                if (in_array($attribute->type, [ "select", "multiselect", "checkbox" ])) {
                    if ($values instanceof Collection || in_array($attribute->type, [ "multiselect", "checkbox" ])) {
                        if ($attribute->slug == "features") {
                            if (!isset($values)) {
                                continue;
                            }
                            $final_value_attribute = $values?->map->only(['id', 'name', 'description', 'image_url'])->values();
                        } else {
                            $final_value_attribute = $values->map(function ($multi_val) {
                                return [
                                    "id" => $multi_val?->id,
                                    "name" => $multi_val?->name,
                                    "code" => $multi_val?->code,
                                ];
                            });
                        }
                    } else {
                        $final_value_attribute = [
                            "id" => $values?->id,
                            "name" => $values?->name,
                            "code" => $values?->code,
                        ];
                        if ($attribute->slug == "tax_class_id") {
                            $data["tax_class"] = $values?->id;
                        }
                    }

                    if (isset($final_value_attribute)) {
                        if (in_array($attribute->slug, $technical_details) && isset($final_value_attribute)) {
                            $data["technical_details"][] = [
                                "title" => $attribute->name,
                                "slug" => $attribute->slug,
                                "value" => $final_value_attribute,
                            ];
                        } else {
                            $data[$attribute->slug] = $final_value_attribute;
                        }
                    }
                    continue;
                }

                if (in_array($attribute->slug, $mainAttribute)) {
                    $data[$attribute->slug] = $values;
                } elseif (in_array($attribute->slug, $technical_details) && isset($values)) {
                    $data["technical_details"][] = [
                        "title" => $attribute->name,
                        "slug" => $attribute->slug,
                        "value" => $values,
                    ];
                } else {
                    $data["attributes"][] = [
                        "title" => $attribute->name,
                        "slug" => $attribute->slug,
                        "value" => $values,
                    ];
                }

                if (($attribute->type == "image" || $attribute->type == "file") && $values) {
                    $data[$attribute->slug] = Storage::url($values);
                }

            }

            $fetched = $product->only(["id", "sku", "status", "website_id", "parent_id", "type"]);
            $inventory = $product->catalog_inventories()->select("quantity", "is_in_stock")->first()?->toArray();
            if (!$inventory) {
                $inventory = [
                    "quantity" => 0,
                    "is_in_stock" => 0,
                ];
            }
            $fetched = array_merge($fetched, $inventory, $data);

            $fetched = $this->product_format_repo->getProductInFormat($fetched, $request, $product);

            if (isset($fetched["disable_animation"])  && isset($fetched["animated_image"])) {
                $fetched = $this->getAnimatedImage($fetched);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function checkGroupByAttributes(object $product): ?object
    {
        try
        {
            if ($product->parent_id) {
                $product = $product->parent;
            }
            $group_by_attribute = $product->attribute_configurable_products()->whereUsedInGrouping(1)->first();
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $group_by_attribute;
    }

    public function getConfigurableData(object $product, object $coreCache, array $product_data): array
    {
        try
        {
            $data_arr = [];
            $this->nested_product = [];
            $this->config_products = [];
            $this->final_product_val = [];

            if ($product->type == "configurable" || ($product->type == "simple" && isset($product->parent_id))) {
                $group_by_attributes = $this->checkGroupByAttributes($product);
                $data_arr["is_group_by_attribute"] = $group_by_attributes ? 1 : 0;

                $this->getConfigurableAttributes($product, $coreCache->store);
                $data_arr["configurable_products"] = $this->final_product_val;

                $product_data = array_merge($product_data, $data_arr);

                if (count($data_arr["configurable_products"]) > 0) {
                    if ($group_by_attributes) {
                        $selected_attr_slug = $group_by_attributes->attribute->slug;
                        $stock_status = collect($data_arr["configurable_products"][$selected_attr_slug])
                            ->where("value", $product_data[$selected_attr_slug]["id"]);
                    } else {
                        $stock_status = collect($data_arr["configurable_products"])->flatten(1);
                    }

                    $stock_statuses = $stock_status->pluck("variations")->flatten(2)->pluck("stock_status")->toArray();
                    $product_data["is_in_stock"] = (in_array(1, $stock_statuses)) ? 1 : 0;
                }
            }

        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $product_data;
    }

    public function getConfigurableAttributes(object $product, object $store): void
    {
        try
        {
            $elastic_variant_products = $this->getConfigurableAttributesFromElasticSearch($product, $store);
            $this->config_products = $elastic_variant_products;

            $this->getVariations($elastic_variant_products);

        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function getProductComponents(object $product, object $store, array $match): object
    {
        try
        {
            $product_builders = $product->productBuilderValues()->whereScope("store")->whereScopeId($store->id)->get();
            if ($product_builders->isEmpty()) {
                $product_builders = $product->getBuilderParentValues($match);
            }

            //fetch from parent product
            if ($product_builders->isEmpty() && $product->parent_id) {
                $product_builders = $product->parent->productBuilderValues()->whereScope("store")->whereScopeId($store->id)->get();
                if ($product_builders->isEmpty()) {
                    $product_builders = $product->parent->getBuilderParentValues($match);
                }
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $product_builders;
    }

    public function getProductImages(object $product, array $data): array
    {
        try
        {
            $data["image"] = $this->getBaseImage($product);

            $data["rollover_image"] = $this->getImages($product, "rollover_image");

            $predicted_galleries = (clone $product)->images()->wherehas("types", function ($query) {
                $query->whereNotIn("slug", ["small_image", "thumbnail_image"]);
            })->get();

            foreach ($predicted_galleries as $gallery) {
                $not_gallery = $product->images()->wherePath($gallery->path)->wherehas("types", function ($query) {
                    $query->whereIn("slug", ["small_image", "thumbnail_image"]);
                })->first();
                if ($not_gallery) {
                    continue;
                }
                $data["gallery"][] = [
                    "url" => Storage::url($gallery->path),
                    "background_color" => $gallery->background_color,
                    "background_size" => $gallery->background_size,
                    "image_background_color" => empty($gallery->image_background_color) ? "white" : $gallery->image_background_color,
                ];
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getAnimatedImage(array $fetched): array
    {
        try
        {
            $fetched["animated_images"] = [
                "animated_image" => $fetched["animated_image"],
                "disable_animation" => $fetched["disable_animation"]
            ];
            unset($fetched["animated_image"], $fetched["disable_animation"] );
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getConfigurableAttributesFromElasticSearch(object $product, object $store): array
    {
        try
        {
            if (isset($product->parent_id)) {
                $product = $product->parent;
            }
            $variant_ids = $product->variants->pluck("id")->toArray();
            $elastic_fetched = [
                "_source" => ["show_configurable_attributes"],
                "size" => count($variant_ids),
                "query" => [
                    "bool" => [
                        "must" => [
                            $this->search_repository->terms("id", $variant_ids)
                        ]
                    ]
                ],
            ];
            $elastic_data =  $this->search_repository->searchIndex($elastic_fetched, $store);
            $elastic_variant_products = isset($elastic_data["hits"]["hits"]) ? collect($elastic_data["hits"]["hits"])->pluck("_source.show_configurable_attributes")->flatten(1)->toArray() : [];
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $elastic_variant_products;
    }

    public function getVariations(array $elastic_variant_products, ?string $key = null)
    {
        try
        {
            $attribute_slugs = collect($elastic_variant_products)->pluck("attribute_slug")->unique()->values()->toArray();
            foreach ($attribute_slugs as $attribute_slug) {
                $state = [];

                $attribute_values = collect($elastic_variant_products)->where("attribute_slug", $attribute_slug)->values()->toArray();
                $variations = collect($elastic_variant_products)->where("attribute_slug", "!=", $attribute_slug)->values()->toArray();
                //$count = collect($attribute_values)->unique("id")->count();

                $j = 0;
                foreach ($attribute_values as $attribute_value) {
                    if (isset($attribute_value["product_status"]) && $attribute_value["product_status"] == 0) {
                        continue;
                    }

                    $append_key = $key ? "$key.{$attribute_slug}.{$j}" : "{$attribute_slug}.{$j}";
                    $product_val_array = [];
                    if (!isset($state[$attribute_value["id"]])) {
                        $state[$attribute_value["id"]] = true;
                        $product_val_array["value"] = $attribute_value["id"];
                        $product_val_array["label"] = $attribute_value["label"];
                        $product_val_array["code"] = $attribute_value["code"];
                        if ($attribute_value["attribute_slug"] == "color") {
                            $status_check_item = collect($attribute_values)->where("id", $attribute_value["id"])->where("product_status", 1)->first();
                            if (!$status_check_item) {
                                continue;
                            }
                            $visibility_item = collect($attribute_values)->where("visibility", 8)->where("id", $attribute_value["id"])->first();
                            
                            $product_val_array["url_key"] = isset($visibility_item["url_key"]) ? $visibility_item["url_key"] : $attribute_value["url_key"];
                            $product_val_array["product_id"] = isset($visibility_item["product_id"]) ? $visibility_item["product_id"] : $attribute_value["product_id"];
                            $product_val_array["parent_id"] = isset($visibility_item["parent_id"]) ? $visibility_item["parent_id"] : $attribute_value["parent_id"];
                            $product_val_array["image"] = isset($visibility_item["image"]) ? $visibility_item["image"] : $attribute_value["image"];
                        }
                        if (count($attribute_slugs) == 1) {
                            $fake_array = array_merge($this->nested_product, [$attribute_value["attribute_slug"] => $attribute_value["id"]]);
                            $dot_product = collect($this->config_products)->where("attribute_combination", $fake_array)->first();
                            $product_val_array["product_id"] = isset($dot_product["product_id"]) ? $dot_product["product_id"] : 0;
                            $product_val_array["parent_id"] = isset($dot_product["parent_id"]) ? $dot_product["parent_id"] : 0;
                            $product_val_array["sku"] = isset($dot_product["product_sku"]) ? $dot_product["product_sku"] : 0;
                            $product_val_array["stock_status"] = isset($dot_product["stock_status"]) ? $dot_product["stock_status"] : 0;
                        }
                        setDotToArray($append_key, $this->final_product_val,  $product_val_array);
                        $j = $j + 1;
                        if (count($attribute_slugs) > 1) {
                            $this->nested_product[ $attribute_value["attribute_slug"] ] = $attribute_value["id"];
                            $this->getVariations($variations, "{$append_key}.variations");
                        }
                    }
                }
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function getBaseImage($product): ?array
    {
        try
        {
            $image = $product->images()->wherehas("types", function($query) {
                $query->whereSlug("base_image");
            })->first();
            if (!$image) {
                $image = $product->images()->first();
            }
            $path = $image ? Storage::url($image->path) : $image;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return [
            "url" => $path,
            "background_color" => $image?->background_color,
            "background_size" => $image?->background_size,
        ];
    }

    public function getImages($product, $image_name): ?array
    {
        try
        {
            $image = $product->images()->wherehas("types", function($query) use($image_name) {
                $query->whereSlug($image_name);
            })->first();
            $path = $image ? Storage::url($image->path) : $image;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return [
            "url" => $path,
            "background_color" => $image?->background_color,
            "background_size" => $image?->background_size,
        ];
    }

    public function getCategory(array $scope, string $category_slug): object
    {
        try
        {
            $category_value = CategoryValue::whereAttribute("slug")->whereValue($category_slug)->firstOrFail();
            $category = $category_value->category;

            if (!$this->categoryRepository->checkStatus($category, $scope)) {
                throw new CategoryNotFoundException();
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $category;
    }

    public function getOptions(object $request, array $category_slugs): ?array
    {
        try
        {
            $category_ids = [];
            $category_filters = [];
            $coreCache = $this->getCoreCache($request);
            $scope = [
                "scope" => "store",
                "scope_id" => $coreCache->store->id
            ];

            $data = $this->categoryRepository->getNestedcategory($coreCache, $scope, $category_slugs, "productFilter");
            $category = $data["category"];


            $layout_type = $category->value($scope, "layout_type");
            if ($layout_type && $layout_type == "multiple") {
                $all_categories = $category->value($scope, "categories");
                foreach ($all_categories as $single_category_id) {
                    $category_data = $this->categoryRepository->query(callback:function ($query) use($single_category_id) {
                        return $query->withDepth()->find($single_category_id);
                    });
                    if (!$category_data) {
                        continue;
                    }
                    $category_ids[] = $single_category_id;

                    $category_filters[] = [
                            "name" => $category_data->value($scope, "name"),
                            "value" => $category_data->id,
                    ];
                }
            } else {
                $category_ids[] = $category->id;
                $category_filters = $this->getCategoryFilters($category, $scope);
            }

            $category_filter_desc = [
                "label" => "Category",
                "name" => "category_ids",
                "values" => $category_filters,
            ];


            $fetched = $this->search_repository->getFilterOptions($category_ids, $coreCache->store, $category_filter_desc);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getCategoryFilters(object $category, array $scope): array
    {
        try
        {
            $filter_data = [];


            $categories = $category->children;

            foreach ($categories as $single_category) {
                if (!$this->categoryRepository->checkMenuStatus($single_category, $scope)) {
                    continue;
                }
                if (!$this->categoryRepository->checkStatus($single_category, $scope)) {
                    continue;
                }

                $filter_data[] = [
                    "name" => $single_category->value($scope, "name"),
                    "value" => $single_category->id,
                ];
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $filter_data;
    }

    public function categoryWiseProduct(object $request, array $category_slugs): ?array
    {
        try
        {
            $fetched = [];

            $coreCache = $this->getCoreCache($request);
            $scope = [
                "scope" => "store",
                "scope_id" => $coreCache->store->id
            ];

            $cache_slug = implode("_", $category_slugs);
            $cache_name = "{$cache_slug}_{$coreCache->website->id}";
            $category_cache = $this->storeCache("category", $cache_name, function () use ($coreCache, $scope, $category_slugs) {
                $data = $this->categoryRepository->getNestedcategory($coreCache, $scope, $category_slugs, "productFetch");
                return $data["category"];
            });

            $category = $this->categoryRepository->fetch($category_cache->id);

            $layout_type = $category->value($scope, "layout_type");
            if (($request->single_layout != 1) && $layout_type && $layout_type == "multiple") {
                $all_categories = $category->value($scope, "categories");
                foreach ($all_categories as $single_category_id) {

                    if ($request->category_ids && !in_array($single_category_id, $request->category_ids)) {
                        continue;
                    }
                    $category_data = $this->categoryRepository->fetch($single_category_id);
                    if (!$category_data) {
                        continue;
                    }

                    $limit = $category->value($scope, "no_of_items");
                    $is_paginated = $category->value($scope, "pagination");

                    $elastic_products = $this->search_repository->getFilterProducts($request, $category_data->id, $coreCache->store, $limit, $is_paginated, "multiple");

                    if (count($elastic_products) > 0) {
                        $category_val = $this->getCategoryOFMultileLayout($category_data, $scope);
                        $category_val = array_merge($category_val, $elastic_products);
                        $fetched["categories"][] = $category_val;
                    }
                }
            } else {
                $limit = $request->limit ?? null;
                $fetched = $this->search_repository->getFilterProducts($request, $category->id, $coreCache->store, $limit);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getCategoryOFMultileLayout(object $category_data, array $scope): ?array
    {
        try
        {
            $fetched = [
                "id" => $category_data->id,
                "slug" => $category_data->value($scope, "slug"),
                "name" => $category_data->value($scope, "name"),
                "hero_banner" => [
                    "background_type" => $category_data->value($scope, "layout_background_type"),
                    "background_image" => $category_data->value($scope, "layout_background_image")
                        ? Storage::url($category_data->value($scope, "layout_background_image"))
                        : null,
                    "youtube_link" => $category_data->value($scope, "layout_youtube_link"),
                    "gradient_color" => $category_data->value($scope, "layout_gradient_color"),
                    "hero_banner_title" => $category_data->value($scope, "layout_hero_banner_title"),
                    "hero_banner_sub_title" => $category_data->value($scope, "layout_hero_banner_sub_title"),
                    "hero_banner_content" =>$category_data->value($scope, "layout_hero_banner_content"),
                    "readmore_label" => $category_data->value($scope, "layout_readmore_label"),
                    "readmore_link" => $category_data->value($scope, "layout_readmore_link"),
                    "section_color" => $category_data->value($scope, "section_color"),
                ]
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function searchWiseProduct(object $request): ?array
    {
        try
        {
            $fetched = [];

            $coreCache = $this->getCoreCache($request);
            $fetched = $this->search_repository->getSearchProducts($request, $coreCache->store);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function searchTestWiseProduct(Request $request): ?array
    {
        try
        {
            $fetched = [];

            $coreCache = $this->getCoreCache($request);
            $fetched["search_products"] = $this->search_repository->getSearchProducts($request, $coreCache->store);
            $fetched["search_categories"] = $this->categoryRepository->geSearchCategories($request, $coreCache);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getUrlkeyWithVisibility(object $product, object $store, ?string $url_key = null): string
    {
        try
        {
            if (isset($product->parent_id)) {
                $group_by_attribute = $product->parent
                    ->attribute_configurable_products()
                    ->whereUsedInGrouping(1)
                    ->first();

                if ($group_by_attribute) {
                    $variant_ids = $product->parent->variants->pluck("id")->toArray();
                    $product_attr = ProductAttribute::query()->with(["value", "product"]);

                    $taken = new Pipe((clone $product_attr));
                    $filter_product_by_group_attr = $taken->pipe($taken->value->whereIn("product_id", $variant_ids))
                        ->pipe($taken->value->whereAttributeId($group_by_attribute->attribute_id))
                        ->pipe($taken->value->get())
                        ->pipe($this->getFilterConfigAttribute($taken->value, $product, $store, $group_by_attribute))
                        ->value;

                    if ($filter_product_by_group_attr) {
                        $visibility_att = Attribute::whereSlug("visibility")->first();

                        $visibility_taken = new Pipe($product_attr);
                        $visibility_product_attr = $visibility_taken->pipe($visibility_taken->value->whereIn("product_id", $filter_product_by_group_attr->pluck("product_id")->toArray()))
                            ->pipe($visibility_taken->value->where("attribute_id", $visibility_att->id))
                            ->pipe($visibility_taken->value->get())
                            ->pipe($this->getFilterVisibility($visibility_taken->value, $visibility_att))
                            ->pipe($visibility_taken->value->first())
                            ->value;

                        if ($visibility_product_attr) {
                            $url_key = $visibility_product_attr->product->value([
                                "scope" => "store",
                                "scope_id" => $store->id,
                                "attribute_slug" => "url_key",
                            ]);
                        }
                    }
                } else {
                    $url_key = $product->parent->value([
                        "scope" => "store",
                        "scope_id" => $store->id,
                        "attribute_slug" => "url_key",
                    ]);
                }

            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $url_key;
    }

    private function getFilterVisibility(object $visibility, object $visibility_att): object
    {
        try
        {
            $visib_attr_option = AttributeOption::whereAttributeId($visibility_att->id)->whereIn("code", ["catalog_search", "catalog"])->get();
            $filter_data =  $visibility->filter(function ($attr_product) use ($visib_attr_option) {
                return in_array($attr_product->value->value, $visib_attr_option->pluck("id")->toArray());
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $filter_data;
    }

    private function getFilterConfigAttribute(object $pipe_value, object $product, object $store, object $group_by_attribute): object
    {
        try
        {
            $group_attribute_option = $product->value([
                "scope" => "store",
                "scope_id" => $store->id,
                "attribute_id" => $group_by_attribute->attribute_id,
            ]);
            $filter_data = $pipe_value->filter(function ($attr_product) use ($group_attribute_option) {
                return ($attr_product->value->value == $group_attribute_option->id);
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
         return $filter_data;
    }
}
