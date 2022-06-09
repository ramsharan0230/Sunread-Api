<?php


namespace Modules\ClubHouse\Repositories;

use Exception;
use Modules\Core\Rules\ScopeRule;
use Illuminate\Support\Facades\DB;
use Modules\ClubHouse\Traits\HasScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Modules\ClubHouse\Entities\ClubHouse;
use Modules\ClubHouse\Rules\SlugUniqueRule;
use Illuminate\Validation\ValidationException;
use Modules\ClubHouse\Entities\ClubHouseValue;
use Modules\ClubHouse\Rules\ClubHouseScopeRule;

class ClubHouseValueRepository
{
    use HasScope;

    // Properties for ClubHouseValueRepositor
    protected $model, $model_key, $club_house_repository, $model_name, $parent_model, $global_file = [];

    /**
     * ClubHouseValueRepositor Constructor
     */
    public function __construct(ClubHouseValue $club_house_value, ClubHouseRepository $club_house_repository, ClubHouse $club_house)
    {
        $this->model = $club_house_value;
        $this->model_key = "clubhouse.values";
        $this->club_house_repository = $club_house_repository;
        $this->model_name = "ClubHouse";
        $this->parent_model = $club_house;

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
            $all_rules = collect(config("clubhouse.attributes"))->pluck("elements")->flatten(1)->map(function($data) {
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
            $exist_club_house = $this->parent_model->findOrFail($id);

            if (isset($request->items[$item["slug"]])) {
                $request_slug = $request->items[$item["slug"]];
                if (isset($request_slug["value"]) && !is_file($request_slug["value"])  && !isset($request_slug["use_default_value"])) {
                $exist_file = $exist_club_house->values()->whereAttribute($item["slug"])->whereScope($request->scope ?? "website")->whereScopeId($request->scope_id ?? $exist_club_house->website_id)->first();
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
    public function validateWithValuesCreate(object $request): array
    {
        try
        {
            $data = $this->club_house_repository->validateData($request, array_merge($this->getValidationRules($request),[
                "items.slug.value" => new SlugUniqueRule($request),
                "website_id" => "required|exists:websites,id"
            ]), function () use ($request) {
                return [
                    "scope" => "website",
                    "scope_id" => $request->website_id
                ];
            });

            if(!isset($data["items"]["slug"]["value"])) $data["items"]["slug"]["value"] = $this->club_house_repository->createUniqueSlug($data);
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
    public function validateWithValuesUpdate(object $request, ?object $club_house = null): array
    {
        try
        {

            $data = $this->club_house_repository->validateData($request, array_merge($this->getValidationRules($request, $club_house?->id, "update"), [
                "items.slug.value" => new SlugUniqueRule($request, $club_house),
                "scope" => "required|in:website,channel,store",
                "scope_id" => [ "required", "integer", "min:1", new ScopeRule($request->scope), new ClubHouseScopeRule($request, $club_house?->id)]
            ]), function () use ($club_house) {
                return [
                    "website_id" => $club_house->website_id
                ];
            });

            if(!isset($data["items"]["slug"]["value"]) && !isset($data["items"]["slug"]["use_default_value"])) $data["items"]["slug"]["value"] = $this->club_house_repository->createUniqueSlug($data, $club_house);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    /**
     * Create or Update ClubHouseValue [Update if it exists, if not create the model]
     */
    public function createOrUpdate(array $data, Model $parent): void
    {
        if ( !is_array($data) || $data == [] ) return;
        DB::beginTransaction();
        try
        {
            $created_data = [];
            $match = [
                "club_house_id" => $parent->id,
                "scope" => $data["scope"],
                "position" => $data["position"],
                "scope_id" => $data["scope_id"]
            ];
            foreach($data["items"] as $key => $val)
            {
                if(in_array($key, $this->global_file)) continue;

                if(isset($val["use_default_value"]) && $val["use_default_value"] != 1) throw ValidationException::withMessages([ "use_default_value" => __("core::app.response.use_default_value") ]);

                if(!isset($val["use_default_value"]) && !array_key_exists("value", $val)) throw ValidationException::withMessages([ "value" => __("core::app.response.value_missing", ["name" => $key]) ]);

                $absolute_path = config("clubhouse.absolute_path.{$key}");
                $configDataArray = config("clubhouse.attributes.{$absolute_path}");

                if($this->scopeFilter($match["scope"], $configDataArray["scope"])) continue;

                $match["attribute"] = $key;

                $value = $val["value"] ?? null;
                $match["value"] = ($configDataArray["type"] == "file" && $value) ? $this->club_house_repository->storeScopeImage($value, "club_house") : $value;

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
