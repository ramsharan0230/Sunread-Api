<?php


namespace Modules\NavigationMenu\Repositories;

use Exception;
use Modules\Core\Rules\ScopeRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Modules\NavigationMenu\Traits\HasScope;
use Illuminate\Validation\ValidationException;
use Modules\Category\Entities\Category;
use Modules\NavigationMenu\Entities\NavigationMenuItem;
use Modules\NavigationMenu\Entities\NavigationMenuItemValue;
use Modules\NavigationMenu\Rules\NavigationMenuItemScopeRule;
use Modules\Page\Entities\Page;

class NavigationMenuItemValueRepository
{
    use HasScope;

    // Properties for NavigationMenuItemValueRepository
    protected $model, $model_key, $navigation_menu_item_repository, $navigation_menu_repository, $model_name, $parent_model, $global_file = [];

    /**
     * NavigationMenuItemValueRepository Constructor
     */
    public function __construct(NavigationMenuItemValue $navigation_menu_item_value, NavigationMenuItemRepository $navigation_item_repository, NavigationMenuRepository $navigation_menu_repository, NavigationMenuItem $navigation_menu_item)
    {
        $this->model = $navigation_menu_item_value;
        $this->model_key = "navigation_menu_item.values";
        $this->navigation_menu_item_repository = $navigation_item_repository;
        $this->model_name = "NavigationMenuItemValue";
        $this->parent_model = $navigation_menu_item;
        $this->navigation_menu_repository = $navigation_menu_repository;

        $this->createModel();
    }

    /**
     * Fetch Validation Rules from Config File and Return it
     */
    public function getValidationRules(object $request, ?int $id = null, ?string $method = null): array
    {

        try
        {
            $scope = $request->scope ?? "website";
            $all_rules = collect(config("navigation_menu.attributes"))->pluck("elements")->flatten(1)->map(function($data) {
                return $data;
            })->reject(function ($data) use ($scope) {
                return $this->scopeFilter($scope, $data["scope"]);
            })->mapWithKeys(function ($item) use ($scope, $id, $method, $request) {
                $prefix = "items.{$item["slug"]}";
                $value_path = "{$prefix}.value";
                $default_path = "{$prefix}.use_default_value";

                $value_rule = ($item["is_required"] == 1) ? (($scope != "website") ? "required_without:{$default_path}" : "required") : "nullable";
                $value_rule = "$value_rule|{$item["rules"]}";
                if($scope != "website") $default_rule = ($item["is_required"] == 1) ? "required_without:{$value_path}|{$item["rules"]}" : "boolean";

                if ($method == "update" && $id && $item["type"] == "file") $value_rule = $this->handleFileIssue($id, $request, $item, $value_rule);

                $rules = [
                    $value_path => $value_rule
                ];
                return isset($default_rule) ? array_merge($rules, [ $default_path => $default_rule ]) : $rules;
            })->toArray();

        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $all_rules;
    }


    /**
     * Handle and Store File from the Request
     */
    public function handleFileIssue(int $id, object $request, array $item, ?string $value_rule): ?string
    {
        try
        {
            $exist_navigation_menu_item = $this->parent_model->findOrFail($id);

            if (isset($request->items[$item["slug"]])) {
                $request_slug = $request->items[$item["slug"]];
                if (isset($request_slug["value"]) && !is_file($request_slug["value"])  && !isset($request_slug["use_default_value"])) {
                $exist_file = $exist_navigation_menu_item->values()->whereAttribute($item["slug"])->whereScope($request->scope ?? "website")->whereScopeId($request->scope_id ?? $exist_navigation_menu_item->website_id)->first();
                    if ($exist_file?->value && (Storage::url($exist_file?->value) == $request_slug["value"])) {
                        $this->global_file[] = $item["slug"];
                        return "";
                    }
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $value_rule;
    }

    /**
     * Validate the Request with Unique Slug and Create it
     */
    public function validateWithValuesCreate(object $request, int $website_id): array
    {
        try
        {
            $data = $this->navigation_menu_item_repository->validateData($request, array_merge($this->getValidationRules($request),[
            ]), function () use ($website_id) {
                return [
                    "scope" => "website",
                    "scope_id" => $website_id
                ];
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    /**
     * Validate the Request win Unique Slug and Update it
     */
    public function validateWithValuesUpdate(object $request, ?object $navigation_menu_item = null): array
    {
        try
        {
            $method = "update";
            $data = $this->navigation_menu_item_repository->validateData($request, array_merge($this->getValidationRules($request, $navigation_menu_item?->id, $method), [
                "scope" => "required|in:website,channel,store",
                "scope_id" => [ "required", "integer", "min:1", new ScopeRule($request->scope), new NavigationMenuItemScopeRule($request, $navigation_menu_item?->id)]
            ]));
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    /**
     * Create or Update NavigationMenuItemValue [Update if it exists, if not create the model]
     */
    public function createOrUpdate(array $data, Model $parent): void
    {
        if ( !is_array($data) || $data == [] ) return;
        DB::beginTransaction();
        try
        {
            $created_data = [];
            $match = [
                "navigation_menu_item_id" => $parent->id,
                "scope" => $data["scope"],
                "scope_id" => $data["scope_id"],
                "status" => isset($data["items"]["status"]["value"]) ? $data["items"]["status"]["value"] : 1,
            ];
            foreach($data["items"] as $key => $val)
            {
                if (in_array($key, $this->global_file)) {
                    continue;
                }

                if (isset($val["use_default_value"]) && $val["use_default_value"] != 1) {
                    throw ValidationException::withMessages([ "use_default_value" => __("core::app.response.use_default_value") ]);
                }

                if (!isset($val["use_default_value"]) && !array_key_exists("value", $val)) {
                    throw ValidationException::withMessages([ "value" => __("core::app.response.value_missing", ["name" => $key]) ]);
                }

                if ($key == "category_id" && isset($val["value"])) {
                    Category::whereId($val["value"])->whereWebsiteId($parent->navigationMenu->website_id)->firstOrFail();
                }

                if ($key == "page_id" && isset($val["value"])) {
                    Page::whereId($val["value"])->whereWebsiteId($parent->navigationMenu->website_id)->firstOrFail();
                }

                $absolute_path = config("navigation_menu.absolute_path.{$key}");
                $configDataArray = config("navigation_menu.attributes.{$absolute_path}");

                if($this->scopeFilter($match["scope"], $configDataArray["scope"])) continue;

                $match["attribute"] = $key;

                $value = $val["value"] ?? null;
                $match["value"] = ($configDataArray["type"] == "file" && $value) ? $this->navigation_menu_item_repository->storeScopeImage($value, "navigation_menu") : $value;

                if($configData = $this->checkCondition($match)->first())
                {
                    if(isset($val["use_default_value"])  && $val["use_default_value"] == 1) $configData->delete();
                    else $created_data["data"][] = $configData->update($match);
                    continue;
                }

                if(isset($val["use_default_value"])  && $val["use_default_value"] == 1) continue;


                $created_data["data"][] = $this->model->create($match);
            }
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
    }
}
