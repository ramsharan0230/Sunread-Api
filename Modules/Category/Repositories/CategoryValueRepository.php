<?php


namespace Modules\Category\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Category\Entities\Category;
use Modules\Category\Entities\CategoryValue;
use Modules\Category\Traits\HasScope;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;
use Modules\Core\Repositories\BaseRepository;

class CategoryValueRepository extends BaseRepository
{
    use HasScope;

    protected array $global_file;
    protected array $global_file_value;

    public function __construct()
    {
        $this->model = new CategoryValue();
        $this->model_key = "catalog.category.values";
        $this->model_name = "Category";

        $this->createModel();
    }

    public function getValidationRules(object $request, ?int $id = null, ?string $method = null): array
    {
        try
        {
            $this->global_file = [];
            $this->global_file_value = [];
            $scope = $request->scope ?? "website";
            $all_rules = collect(config("category.attributes"))->pluck("elements")->flatten(1)->map(function($data) {
                return $data;
            })->reject(function ($data) use ($scope, $request) {
                $condition_state = $this->checkConditions($data, $request);
                return ($this->scopeFilter($scope, $data["scope"]) || $condition_state);
            })->mapWithKeys(function ($item) use ($scope, $id, $method, $request) {

                $prefix = "items.{$item["slug"]}";
                $value_path = "{$prefix}.value";
                $default_path = "{$prefix}.use_default_value";

                $value_rule = ($item["is_required"] == 1) ? (($scope != "website") ? "required_without:{$default_path}" : "required") : "nullable";
                $value_rule = "$value_rule|{$item["rules"]}";
                if ($scope != "website") {
                    $default_rule = ($item["is_required"] == 1) ? "required_without:{$value_path}" : "boolean";
                }

                if ($method == "update" && $id && $item["type"] == "file") {
                    $value_rule = $this->handleFileIssue($id, $request, $item, $value_rule);
                }

                $rules = [
                    $value_path => $value_rule
                ];

                if ((($item["type"] == "select" && isset($item["multiple"])) || $item["type"] == "checkbox" || $item["type"] == "categoryselect") && isset($item["value_rules"]))  {

                    $rules = array_merge($rules, [
                        "{$value_path}.*" => $item["value_rules"]
                    ]);
                }

                return isset($default_rule) ? array_merge($rules, [ $default_path => $default_rule ]) : $rules;
            })->toArray();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $all_rules;
    }

    public function handleFileIssue(
        int $id,
        object $request,
        array $item,
        ?string $value_rule
    ): ?string {
        try
        {
            $exist_category = Category::findOrFail($id);
            $scope["scope"] = $request->scope ?? "website";
            $scope["scope_id"] = $request->scope_id ?? $exist_category->website_id;

            if (isset($request->items[$item["slug"]])) {
                $request_slug = $request->items[$item["slug"]];
                if (isset($request_slug["value"]) && !is_file($request_slug["value"])  && !isset($request_slug["use_default_value"])) {
                $exist_file = $exist_category->values()->whereAttribute($item["slug"])->whereScope($scope["scope"])->whereScopeId($scope["scope_id"])->first();
                    if ($exist_file?->value && (Storage::url($exist_file?->value) == $request_slug["value"])) {
                        $this->global_file[] = $item["slug"];
                        return "";
                    } else {
                        return $this->checkOnParentScope($exist_category, $scope, $item, $request_slug["value"], $value_rule);
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

    public function checkOnParentScope(
        object $category,
        array $scope,
        array $item,
        string $value,
        ?string $value_rule
    ): ?string {
        try
        {
            if ($scope["scope"] != "website") {
                switch ($scope["scope"]) {
                    case "store":
                        $input["scope"] = "channel";
                        $input["scope_id"] = Store::find($scope["scope_id"])->channel->id;
                        break;

                    case "channel":
                        $input["scope"] = "website";
                        $input["scope_id"] = Channel::find($scope["scope_id"])->website->id;
                        break;
                }

                $exist_file = $category->values()->whereAttribute($item["slug"])->whereScope($input["scope"])->whereScopeId($input["scope_id"])->first();

                if ($exist_file?->value && (Storage::url($exist_file?->value) == $value)) {
                    $file_value = [
                        "attribute" => $item["slug"],
                        "value" => $exist_file?->value
                    ];
                    $this->global_file[] = $item["slug"];
                    $this->global_file_value[] = $file_value;
                    return "";
                } else {
                    return $this->checkOnParentScope($category, $input, $item, $value, $value_rule);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $value_rule;
    }

    public function createOrUpdateValue(array $data, Model $parent): void
    {
        if ( !is_array($data) || $data == [] ) {
            return;
        }

        DB::beginTransaction();
        Event::dispatch("{$this->model_key}.create.before");

        try
        {
            $created_data = [];
            $match = [
                "category_id" => $parent->id,
                "scope" => $data["scope"],
                "scope_id" => $data["scope_id"]
            ];

            foreach ($data["items"] as $key => $val) {
                if (in_array($key, $this->global_file)) {
                    continue;
                }

                if (isset($val["use_default_value"]) && $val["use_default_value"] != 1) {
                    throw ValidationException::withMessages([ "use_default_value" => __("core::app.response.use_default_value") ]);
                }

                if (!isset($val["use_default_value"]) && !array_key_exists("value", $val)) {
                    throw ValidationException::withMessages([ "value" => __("core::app.response.value_missing", ["name" => $key]) ]);
                }

                $absolute_path = config("category.absolute_path.{$key}");
                $configDataArray = config("category.attributes.{$absolute_path}");

                if ($this->scopeFilter($match["scope"], $configDataArray["scope"]) || !isset($val["value"])) {
                    continue;
                }

                $match["attribute"] = $key;

                $value = $val["value"] ?? null;
                $match["value"] = ($configDataArray["type"] == "file" && $value) ? $this->storeScopeImage($value, "category") : $value;

                //delete dependent attributes checking conditions
                if (isset($configDataArray["dependent_attributes"])) {
                    foreach ($configDataArray["dependent_attributes"] as $dependent_attribute) {
                        $child_absolute_path = config("category.absolute_path.{$dependent_attribute}");
                        $child_configDataArray = config("category.attributes.{$child_absolute_path}");

                        if ($value != $child_configDataArray["conditions"]["value"]) {
                            $this->value_model->whereCategoryId($parent->id)
                            ->whereScope($match["scope"])
                            ->whereScopeId($match["scope_id"])
                            ->whereAttribute($dependent_attribute)
                            ->delete();
                        }
                    }
                }

                $configData = $this->checkCondition($match)->first();
                if ($configData) {
                    if (isset($val["use_default_value"])  && $val["use_default_value"] == 1) {
                        $configData->delete();
                    } else {
                        $created_data["data"][] = $configData->update($match);
                    }
                    continue;
                }
                if (isset($val["use_default_value"])  && $val["use_default_value"] == 1) {
                    continue;
                }
                $created_data["data"][] = $this->model->create($match);
            }

            //parent image for child scope
            foreach($this->global_file_value as $file_value) {
                $match = array_merge($match, $file_value);
                $created_data["data"][] = $this->model->create($match);
            }
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        Event::dispatch("{$this->model_key}.create.after", $created_data);
        DB::commit();
    }

    public function checkConditions(array $element, object $request): bool
    {
        try
        {
            $items = $request->items;
            if (count($element["conditions"]) > 0) {
                $field = $element["conditions"]["field"];
                $val = $element["conditions"]["value"];
                return (isset($items[$field]["value"]) && $items[$field]["value"] != $val);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return false;
    }

    public function getQueryValue(
        string $attribute,
        ?int $parent_id,
        ?int $category_id,
        ?int $scope_id,
        ?string $scope
    ): ?object {
        try
        {
            $fetched = $this->query(function ($query) use ($parent_id, $category_id, $scope_id, $scope, $attribute) {
                return $query->whereHas("category", function ($query) use ($parent_id) {
                    $query->whereParentId($parent_id);
                })->whereAttribute($attribute)
                    ->whereCategoryId($category_id)
                    ->whereScope($scope)
                    ->whereScopeId($scope_id)
                    ->first();
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

}
