<?php

namespace Modules\Category\Repositories\StoreFront;

use Exception;
use Modules\Category\Entities\Category;
use Modules\Category\Entities\CategoryValue;
use Modules\Category\Exceptions\CategoryNotFoundException;
use Modules\Category\Repositories\CategoryValueRepository;
use Modules\Category\Transformers\StoreFront\CategoryResource;
use Modules\Core\Facades\SiteConfig;
use Modules\Core\Repositories\BaseRepository;

class CategoryRepository extends BaseRepository
{
    protected $category_value_repository;
    protected array $page_groups;

    public function __construct()
    {
        $this->model = new Category();
        $this->model_key = "catalog.categories";
        $this->category_value_repository = new CategoryValueRepository();
        $this->page_groups = [
            "hero_banner",
            "usp_banner_1",
            "usp_banner_2",
            "usp_banner_3",
        ];
    }

    public function checkMenuStatus(object $category, array $scope): bool
    {
        try
        {
            $include_value = $category->value($scope, "include_in_menu");
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (isset($include_value) && $include_value == "1");
    }

    public function checkStatus(object $category, array $scope): bool
    {
        try
        {
            $status_value = $category->value($scope, "status");
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (isset($status_value) && $status_value == "1");
    }

    public function getMenu(object $request): array
    {
        try
        {
            $fetched = [];
            $coreCache = $this->getCoreCache($request);

            $categories = $this->model->withDepth()->having('depth', '=', 0)->whereWebsiteId($coreCache->website->id)->get();
            $scope = [
                "scope" => "store",
                "scope_id" => $coreCache->store->id
            ];

            $fetched["categories"] = $this->getCategories($categories, $scope);

            $fetched["logo"] = SiteConfig::fetch("logo", "channel", $coreCache->channel->id);
            $fetched["footer_logo"] = SiteConfig::fetch("footer_logo", "channel", $coreCache->channel->id);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getCategories(object $categories, array $scope): array
    {
        try
        {
            $fetched = [];
            foreach ($categories as $category) {
                if (!$this->checkMenuStatus($category, $scope)) {
                    continue;
                }
                if (!$this->checkStatus($category, $scope)) {
                    continue;
                }
                $fetched[] = new CategoryResource($category);
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getCategoryData(object $request, array $category_slugs): ?array
    {
        try
        {
            $fetched = [];

            $coreCache = $this->getCoreCache($request);
            $scope = [
                "scope" => "store",
                "scope_id" => $coreCache->store->id
            ];

            $all_fetched_data = $this->getNestedcategory($coreCache, $scope, $category_slugs);
            $category = $all_fetched_data["category"];

            $fetched["category"] = $this->getCategoryDetails($category, $scope);
            $fetched["navigation"] = $this->getNavigation($category, $scope, $category_slugs);
            $fetched["breadcrumbs"] = $all_fetched_data["breadcrumbs"];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getNestedcategory(
        object $coreCache,
        array $scope,
        array $slugs,
        ?string $type = null
    ): array {
        try
        {
            $parent_id = null;
            $fetched = [];
            $custom_url = [];

            if ($type) {
                $count = count($slugs);
                unset($slugs[--$count]);
                if ($type == "productFilter") {
                    unset($slugs[--$count]);
                }
            }

            foreach ($slugs as $slug) {
                $category_slug = $this->category_value_repository->query(function ($query) use ($parent_id, $slug, $coreCache) {
                    return $query->whereHas("category", function ($query) use ($parent_id, $coreCache) {
                        $query->whereParentId($parent_id)->whereWebsiteId($coreCache->website->id);
                    })->whereAttribute("slug")->whereValue($slug)->firstorFail();
                });

                $category = $category_slug->category;
                $parent_id = $category_slug->category_id;

                if (isset($category_slug->scope)) {
                    if (in_array($category_slug->scope, ["channel", "website"])) {
                        $this->checkScopeForUrlKey($category_slug?->category_id, $coreCache, $category_slug?->scope, $parent_id);
                    }
                    if ($category_slug->scope == "store" && $category_slug?->scope_id != $coreCache->store->id) {
                        throw new CategoryNotFoundException();
                    }
                }

                if (!$this->checkStatus($category, $scope)) {
                    throw new CategoryNotFoundException();
                }

                if (!$type) {
                    $custom_url[] = $slug;

                    $fetched["breadcrumbs"][] = [
                        "id" => $category->id,
                        "slug" => $category->value($scope, "slug"),
                        "name" => $category->value($scope, "name"),
                        "url" => implode("/", $custom_url)
                    ];
                }
                $fetched["category"] = $this->query(function ($query) use($category) {
                    return $query->withDepth()->find($category->id);
                });
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getCategoryDetails(object $category, array $scope): array
    {
        try
        {
            $data = [];

            $basic_data = ["name", "slug", "description", "layout_type", "categories", "no_of_items", "pagination"];
            $meta_data = ["meta_title", "meta_keywords", "meta_description"];
            $config_fields = config("category.attributes");

            $data["id"] = $category->id;
            foreach ($basic_data as $key) {
                $data[$key] = $category->getCategoryVal($scope, $key);
            }
            foreach ($meta_data as $key) {
                $data["seo"][$key] = $category->getCategoryVal($scope, $key);
            }

            foreach ($this->page_groups as $group) {
                $item = [];
                $slugs = collect($config_fields[$group]["elements"])->pluck("slug");
                foreach ($slugs as $slug) {
                    $item[$slug] = $category->getCategoryVal($scope, $slug);
                }
                $data["pages"][$group] = $item;
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getNavigation(object $category, array $scope, array $slugs): array
    {
        try
        {
            $fetched = [];
            $category = $this->query(function ($query) use($category) {
                return $query->withDepth()->find($category->id);
            });

            $fetched["parent"] = $this->getParentNavigation($category, $scope, $slugs);
            $fetched["children"] = $this->getChildNavigation($category, $scope, $slugs);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getParentNavigation(object $category, array $scope, array $slugs): array
    {
        try
        {
            $parent_data = [];
            $parent_categories = [];

            $count = count($slugs);

            if ($category->parent ) {
                if ($category->depth == 1) {
                    $parent_categories = $category->parent->children;
                } else {
                    $parent_categories = $category->parent->parent->children;
                    unset($slugs[--$count]);
                }
            }

            foreach ($parent_categories as $single_parent_category) {
                if (!$this->checkMenuStatus($single_parent_category, $scope)) {
                    continue;
                }
                if (!$this->checkStatus($single_parent_category, $scope)) {
                    continue;
                }

                $slug =  $single_parent_category->value($scope, "slug");
                $slugs[$count-1] = $slug;

                $parent_data[] = [
                    "id" => $single_parent_category->id,
                    "slug" => $slug,
                    "name" => $single_parent_category->value($scope, "name"),
                    "url" => implode("/", $slugs)
                ];
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $parent_data;
    }

    public function getChildNavigation(object $category, array $scope, array $slugs): array
    {
        try
        {
            $child_data = [];

            $count = count($slugs);
            if ($category->parent && $category->depth == 2) {
                $categories = $category->parent->children;
                $fake_slugs = $slugs;
                unset($fake_slugs[$count-1]);
                $url_count = $count - 1;
            } else {
                $categories = $category->children;
                $url_count = ++$count;
            }

            foreach($categories as $single_category) {
                if (!$this->checkMenuStatus($single_category, $scope)) {
                    continue;
                }
                if (!$this->checkStatus($single_category, $scope)) {
                    continue;
                }

                $slug =  $single_category->value($scope, "slug");
                $slugs[$url_count] = $slug;

                $child_data[] = [
                    "id" => $single_category->id,
                    "slug" => $slug,
                    "name" => $single_category->value($scope, "name"),
                    "url" => implode("/", $slugs)
                ];
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $child_data;
    }

    public function checkScopeForUrlKey(?int $category_id, object $coreCache, ?string $custom_scope, ?int $parent_id): void
    {
        try
        {
            $attribute = "slug";
            if ($custom_scope == "channel") {
                $scope_product_attr = $this->category_value_repository->getQueryValue($attribute, $parent_id, $category_id, $coreCache->store->id, "store");

                if ($scope_product_attr) {
                    throw new CategoryNotFoundException();
                }
            }
            if ($custom_scope == "website") {
                $scope_product_attr = $this->category_value_repository->getQueryValue($attribute, $parent_id, $category_id, $coreCache->channel->id, "channel");

                if ($scope_product_attr) {
                    throw new CategoryNotFoundException();
                } else {
                    $this->checkScopeForUrlKey($category_id, $coreCache, "channel", $parent_id);
                }
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function geSearchCategories(object $request, object $coreCache): array
    {
        try
        {
            $category_values = $this->category_value_repository->query(function ($query) use ($request) {
                return $query->whereAttribute("name")->whereLike("value", $request->q)->get();
            });
            $scope = [
                "scope" => "store",
                "scope_id" => $coreCache->store->id,
            ];

            $fetched = [];

            foreach ($category_values as $category_value) {
                $single_category = $category_value->category;

                if (isset($category_value->scope)) {
                    if (in_array($category_value->scope, ["channel", "website"])) {
                        $status = $this->checkScopeForAttribute($category_value?->category_id, $coreCache, "name", $category_value?->scope);

                        if ($status) {
                            continue;
                        }
                    }
                    if ($category_value->scope == "store" && $category_value?->scope_id != $coreCache->store->id) {
                        continue;
                    }
                }

                if (!$this->checkStatus($single_category, $scope)) {
                    continue;
                }

                $slugs = [];
                $parent_status = 0;

                foreach ($single_category->ancestors as $ancestor) {
                    if (!$this->checkStatus($ancestor, $scope)) {
                        $parent_status = 1;
                        break;
                    }
                    $slugs[] = $ancestor->value($scope, "slug");
                }

                if ($parent_status == 1) {
                    continue;
                }

                $slugs[] = $single_category->value($scope, "slug");

                $fetched["categories"][] = [
                    "id" => $single_category->id,
                    "slug" => $single_category->value($scope, "slug"),
                    "name" => $single_category->value($scope, "name"),
                    "url" => implode("/", $slugs),
                    "image" => $single_category->getCategoryVal($scope, "image"),
                ];
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function checkScopeForAttribute(
        ?int $category_id,
        object $coreCache,
        string $attribute,
        ?string $custom_scope,
        ?int $parent_id = null
    ): bool {
        try
        {
            if ($custom_scope == "channel") {
                $scope_product_attr = $this->category_value_repository->getQueryValue($attribute, $parent_id, $category_id, $coreCache->store->id, "store");

                if ($scope_product_attr) {
                    return true;
                }
            }
            if ($custom_scope == "website") {
                $scope_product_attr = $this->category_value_repository->getQueryValue($attribute, $parent_id, $category_id, $coreCache->channel->id, "channel");

                if ($scope_product_attr) {
                    return true;
                } else {
                    $this->checkScopeForAttribute($category_id, $coreCache, $attribute, "channel", $parent_id);
                }
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return false;
    }
}

