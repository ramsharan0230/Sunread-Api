<?php

namespace Modules\Product\Repositories;

use Exception;
use Illuminate\Validation\ValidationException;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;
use Modules\Core\Facades\PriceFormat;
use Modules\Core\Facades\SiteConfig;
use Modules\Product\Entities\Product;
use Modules\Product\Jobs\SingleIndexing;
use Modules\Product\Repositories\StoreFront\ProductFormatRepository;
use Modules\Product\Traits\ElasticSearch\HasIndexing;
use Modules\Tax\Facades\NewTaxPrice;

class ProductSearchRepository extends ElasticSearchRepository
{
    use HasIndexing;

    protected $model;
    protected array $listSource;
    protected $product_format_repo;

    public function __construct()
    {
        $this->model = new Product();
        $this->product_format_repo = new ProductFormatRepository();

        $this->listSource = [ "id", "parent_id", "website_id", "name", "sku", "type", "is_in_stock", "stock_status_value", "url_key", "quantity", "visibility", "visibility_value", "price", "special_price", "special_from_date", "special_to_date", "new_from_date", "new_to_date", "base_image", "thumbnail_image", "rollover_image", "color", "color_value", "tax_class_id", "configurable_attributes"];
    }

    public function search(object $request): array
    {
        try
        {
            $search = [];
            $searchKeys = Attribute::where('is_searchable', 1)->pluck('slug')->toArray();

            if (isset($request->q)) {
                foreach($searchKeys as $key) $search[] = $this->match_phrase_prefix($key, $request->q);
            }
            $query = $this->orwhereQuery($search);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return [
            "query" => $query,
            "sort" => []
        ];
    }

    public function arrangeCategoryFilter(?object $request = null, ?int $category_id = null, ?string $layout_type = null): array
    {
        try
        {
            $category_ids = [];

            if ($category_id && (!$request->category_ids || $layout_type == "multiple")) {
                $category_ids[]= $category_id;
            }

            if($request->category_ids && $layout_type != "multiple") {
                $category_ids = array_merge($category_ids, $request->category_ids);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $category_ids;
    }

    public function filterAndSort(?object $request = null, ?array $category_ids = []): array
    {
        try
        {
            $filter = [];
            $sort = [];

            $attributeFilterKeys = Attribute::where('use_in_layered_navigation', 1)->pluck('slug')->toArray();

            if (count($category_ids) > 0) {
                $filter[]= $this->terms("categories.id", $category_ids);
            }

            if ($request && count($request->all()) > 0) {
                if ($request->sort_by) {
                    $sort = $this->sort($request->sort_by, $request->sort_order ?? "asc");
                }

                foreach($request->all() as $key => $value) {
                    if (in_array($key, $attributeFilterKeys) && $value) {
                        if ($key == "size" || $key == "color") {
                            $size = [$this->terms("configurable_{$key}", $value), $this->terms($key, $value)];
                            $filter[] = $this->OrwhereQuery($size);
                        } else {
                            $filter[] = $this->terms($key, $value);
                        }
                    }
                }
            }

            $query = $this->whereQuery($filter);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return [
            "query" => $query,
            "sort" => $sort
        ];
    }

    public function categoryFilter(array $category_id): array
    {
        try
        {
            $filter = [];
            $sort = [];

            if ($category_id) {
                $filter[]= $this->terms("categories.id", $category_id);
            }

            $query = $this->whereQuery($filter);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return [
            "query" => $query,
            "sort" => $sort
        ];
    }

    public function getStore(object $request): object
    {
        try
        {
            $website = Website::whereHostname($request->header("hc-host"))->firstOrFail();
            $store = Store::whereCode($request->header("hc-store"))->firstOrFail();

            if ($store->channel->website->id != $website->id) {
                throw ValidationException::withMessages(["hc-store" => "Store does not belong to this website"]);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $store;
    }

    public function getFilterProducts(
        object $request,
        int $category_id,
        object $store,
        ?int $limit = null,
        ?int $is_paginated = 1,
        ?string $layout_type = null
    ): ?array {
        try
        {
            $category_ids = $this->arrangeCategoryFilter($request, $category_id, $layout_type);
            $filter = $this->filterAndSort($request, $category_ids);
            if (!$limit) {
                $limit = SiteConfig::fetch("pagination_limit", "global", 0) ?? 10;
            }
            $products = $this->getProductWithPagination($filter, $request, $store, $limit, $is_paginated, "catalog");
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $products;
    }

    public function getSearchProducts(object $request, object $store, ?int $is_paginated = 1): ?array
    {
        try
        {
            $filter = $this->search($request);
            $limit = SiteConfig::fetch("pagination_limit", "global", 0) ?? 10;
            $products = $this->getProductWithPagination($filter, $request, $store, $limit, $is_paginated, "search");
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $products;
    }

    public function getProductWithPagination(
        array $filter,
        object $request,
        object $store,
        int $limit,
        ?int $is_paginated = 1,
        ?string $visibility = null
    ): ?array {
        try
        {
            $data = [];
            $data = $this->finalQuery($filter, $request, $store, $limit, $visibility, $is_paginated);
            $total = isset($data["products"]["hits"]["total"]["value"]) ? $data["products"]["hits"]["total"]["value"] : 0;
            $products = isset($data["products"]["hits"]["hits"]) ? collect($data["products"]["hits"]["hits"])->pluck("_source")->toArray() : [];
            $data["products"] = $products;

            if ($is_paginated == 1) {
                $data["last_page"] = (int) ceil($total/$data["limit"]);
                $data["total"] = $total;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function productWithFormat(object $request, array $products, object $store): array
    {
        try
        {
            $tax_data = $this->getTaxCalcData($request);
            $price_data = $this->getPriceCalcData($store);

            foreach($products as &$product) {
                $product = $this->product_format_repo->getProductListInFormat($product, $tax_data, $price_data);
                $product = $this->product_format_repo->changeProductStockStatus($product);

                $product["image"] = isset($product["thumbnail_image"]) ? $product["thumbnail_image"] : $product["base_image"];
                $product["quantity"] = isset($product["quantity"]) ? decodeJsonNumeric($product["quantity"]) : 0;
                $product["color"] = isset($product["color"]) ? $product["color"] : null;
                $product["color_value"] = isset($product["color_value"]) ? $product["color_value"] : null;
                unset($product["thumbnail_image"], $product["base_image"]);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $products;
    }

    public function getTaxCalcData(object $request): ?array
    {
        try
        {
            $tax_data = [];
            $config_data = NewTaxPrice::getConfigValue($request);
            $country_data = NewTaxPrice::getCountryAndZipCode($config_data);
            $tax_rules = NewTaxPrice::getTaxRulesWithRateInList($country_data["country"], $country_data["zip_code"]);

            $tax_data = [
                "config_data" => $config_data,
                "tax_rules" => $tax_rules
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $tax_data;
    }

    public function getPriceCalcData(object $store): ?array
    {
        try
        {
            $price_data = [];

            $price_config_data = PriceFormat::getConfigurationData("store", $store->id);
            $price_data["group_separator_value"] = PriceFormat::group_separator($price_config_data->group_separator);
            $price_data["decimal_separator_value"] = PriceFormat::decimal_separator($price_config_data->decimal_separator);
            $price_data["withminus"] = PriceFormat::minusSignPosition($price_config_data->minus, "price", $price_config_data->minus_position, $price_config_data->currency_symbol, $price_config_data->currency_position);
            $price_data["withoutminus"] = PriceFormat::symbolPosition($price_config_data->currency_symbol, "price", $price_config_data->currency_position);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $price_data;
    }

    public function getProduct(object $request): ?array
    {
        try
        {
            $data = [];
            $data[] = $this->search($request);

            $filter = $this->filterAndSort($request);
            $data[] = $filter["query"];

            $query = $this->whereQuery($data);
            $final_query = [];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $final_query;
    }

    public function aggregation(): ?array
    {
        try
        {
            $aggregate = [];
            $staticFilterKeys = ["color", "size", "collection", "configurable_size", "configurable_color"];
            foreach($staticFilterKeys as $field) {
                $aggregate[$field] = $this->aggregate($field);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $aggregate;
    }

    public function getFilterOptions(array $category_ids, object $store, array $category_filters): ?array
    {
        try
        {
            $filters = [];
            $filters[] = $category_filters;
            $data = $this->categoryFilter($category_ids);
            $aggregate = $this->aggregation();

            $final_l[] = $data["query"];
            $final_l[] = $this->term("list_status", 1);
            $final_l[] = $this->term("product_status", 1);
            $final_l[] = $this->terms("visibility_code", [ "catalog", "catalog_search" ]);

            $final_q = $this->whereQuery($final_l);

            $query = [
                "size"=> 0,
                "query"=> $final_q,
                "aggs"=> $aggregate
            ];

            $fetched = $this->searchIndex($query, $store);

            $fetched["aggregations"]["size"]["buckets"] = array_merge($fetched["aggregations"]["size"]["buckets"], $fetched["aggregations"]["configurable_size"]["buckets"]);
            $fetched["aggregations"]["color"]["buckets"] = array_merge($fetched["aggregations"]["color"]["buckets"], $fetched["aggregations"]["configurable_color"]["buckets"]);

            foreach(["color", "size", "collection"] as $field) {
                $state = [];
                $filter["label"] = Attribute::whereSlug($field)->first()?->name;
                $filter["name"] = $field;
                $filter["values"] = collect($fetched["aggregations"][$field]["buckets"])->map(function($bucket) use(&$state) {
                    if (!in_array($bucket["key"], $state)) {
                        $state[] = $bucket["key"];
                        return [
                            "name" => AttributeOption::find($bucket["key"])?->name,
                            "value" =>  $bucket["key"]
                        ];
                    }
                })->filter()->values();
                $filters[] = $filter;

            }

            $filters[] = [
                "label" => "Sort By",
                "name" => "sort_by",
                "values" => [
                    [
                        "label" => "Name",
                        "name" => "name",
                        "value" => "name",
                    ],
                    [
                        "label" => "Price",
                        "name" => "price",
                        "value" => "price",
                    ]
                ]

            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $filters;
    }

    public function finalQuery(
        array $filter,
        object $request,
        object $store,
        int $limit,
        ?string $visibility,
        ?int $is_paginated = 1
    ): ?array {
        try
        {
            $page = $request->page ?? 1;

            $final_l[] = $filter["query"];
            $final_l[] = $this->term("list_status", 1);
            $final_l[] = $this->term("product_status", 1);
            $final_l[] = $this->terms("visibility_code", [ $visibility, "catalog_search" ]);

            $final_q = $this->whereQuery($final_l);

            $fetched = [
                "_source" => $this->listSource,
                "from"=> ($page-1) * $limit,
                "size"=> $limit,
                "query"=> $final_q,
                "sort" => (count($filter["sort"]) > 0) 
                    ? $filter["sort"]
                    : [
                        [
                            "id" => [
                                "order" => "desc",
                            ],
                        ],
                    ],
            ];

            $data =  $this->searchIndex($fetched, $store);
            $final_data = ($is_paginated == 1) ? [
                "products" => $data,
                "current_page" => (int) $page,
                "limit" => (int) $limit
            ] : [
                "products" => $data
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $final_data;
    }

    public function reIndex(int $id, ?callable $callback = null): object
    {
        try
        {
            $indexed = $this->model->findOrFail($id);
            if ($callback) {
                $callback($indexed);
            }
            $stores = Website::find($indexed->website_id)->channels->map(function ($channel) {
                return $channel->stores;
            })->flatten(1);

            foreach($stores as $store) SingleIndexing::dispatch($indexed, $store);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $indexed;
    }

    public function bulkReIndex(object $request, ?callable $callback = null): object
    {
        try
        {
            $request->validate([
                'ids' => 'array|required',
                'ids.*' => 'required',
            ]);

            $indexed = $this->model->whereIn('id', $request->ids)->get();
            if ($callback) {
                $callback($indexed);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $indexed;
    }
}
