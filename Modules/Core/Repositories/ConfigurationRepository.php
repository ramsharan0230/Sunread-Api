<?php

namespace Modules\Core\Repositories;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Configuration;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;
use Modules\Core\Repositories\BaseRepository;
use Modules\Core\Rules\ScopeRule;
use Modules\Core\Traits\Configuration as TraitsConfiguration;
use Illuminate\Support\Facades\Redis;

class ConfigurationRepository extends BaseRepository
{
    protected $channel_model, $store_model, $global_file_slug, $global_file_value;
    use TraitsConfiguration;

    public function __construct(Configuration $configuration, Website $website_model, Channel $channel_model, Store $store_model)
    {
        $this->model = $configuration;
        $this->model_key = "core.configuration";
        $this->rules = [
            "scope" => [ "sometimes", "in:global,website,channel,store" ]
        ];
        $this->createModel();

        $this->website_model = $website_model;
        $this->channel_model = $channel_model;
        $this->store_model = $store_model;
    }

    public function getConfigFile(): object
    {
        try
        {
            $config_data = config("configuration");
            $config_fields = collect($config_data)->sortBy("position");
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $config_fields;
    }

    public function getConfigData(object $request): array
    {
        try
        {
            $this->validateData($request, $this->scopeValidation($request));

            $fetched = $this->getConfigFile()->toArray();
            $checkKey = [ "scope" => $request->scope ?? "global", "scope_id" => $request->scope_id ?? 0 ];
            if($checkKey["scope"] != "global") $fetched_data['entity'] = $this->getEntityData($checkKey);

            foreach($fetched as $key => $data)
            {
                if(!isset($data["children"])) continue;
                foreach($data["children"] as $i => $children)
                {
                    if(!isset($children["subChildren"])) continue;
                    foreach($children["subChildren"] as $j => $subchildren)
                    {
                        if(!isset($subchildren["elements"])) continue;

                        $subchildren_data["title"] = $subchildren["title"];
                        $subchildren_data["elements"] = [];

                        foreach($subchildren["elements"] as $k => &$element)
                        {
                            if($this->scopeFilter($checkKey["scope"], $element["scope"])) continue;

                            $checkKey["path"] = $element["path"];
                            $checkKey["provider"] = $element["provider"];

                            $existData = $this->has((object) $checkKey);
                            if($checkKey["scope"] != "global") $element["use_default_value"] = $existData ? 0 : 1;
                            $element["default"] = $existData ? $this->getValues((object) $checkKey) : $this->getDefaultValues((object)$checkKey, $element["default"]);

                            if(is_array($element["default"])) {
                                $element["default"] = array_map(function($array_val) {
                                    return decodeJsonNumeric($array_val);
                                }, $element["default"]);
                            }
                            else {
                                $element["default"] = decodeJsonNumeric($element["default"]);
                            }

                            if($element["type"] == "file" && $element["default"]) $element["default"] = Storage::url($element["default"]);

                            if( $element["provider"] !== "") $element["options"] = $this->cacheQuery($element, $request, $checkKey);
                            $element["absolute_path"] = $key.".children.".$i.".subChildren.".$j.".elements.".$k;

                            unset($element["pluck"], $element["provider"], $element["rules"], $element["showIn"]);
                            $subchildren_data["elements"][] = $element;
                        }
                        $children["subChildren"][$j] = $subchildren_data;
                    }
                    $data["slug"] = Str::slug($data["title"]);
                    $data["children"][$i] = $children;
                    $data["children"][$i]["absolute_path"] = "{$key}.children.{$i}.subChildren";
                    $data["children"][$i]["slug"] = Str::slug($children["title"]);
                }
                $fetched_data['config'][$key] = $data;
            }
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return $fetched_data;
    }

    public function getEntityData(array $data): array
    {
        $input = [];
        switch($data["scope"])
        {
            case "website":
                $input["name"] = $this->website_model->findorFail($data["scope_id"])->name;
                break;

            case "channel":
                $channel = $this->channel_model->findorFail($data["scope_id"]);
                $input["name"] = $channel->name;
                $input["website_id"] = $channel->website_id;
                break;

            case "store":
                $store = $this->store_model->findorFail($data["scope_id"]);
                $input["name"] = $store->name;
                $input["website_id"] = $store->channel->website_id;
                break;
        }
        return $input;

    }

    public function scopeFilter(string $scope, string $element_scope): bool
    {
        if($scope == "website" && in_array($element_scope, ["global"])) return true;
        if($scope == "channel" && in_array($element_scope, ["global", "website"])) return true;
        if($scope == "store" && in_array($element_scope, ["global", "website", "channel"])) return true;
        return false;
    }

    public function scopeValidation(object $request)
    {
        return ((isset($request->scope) && $request->scope != "global") || isset($request->scope_id)) ? [
            "scope_id" => ["required", "integer", "min:0", new ScopeRule($request->scope)]
        ] : [];
    }

    public function getValidationRules(object $request): array
    {
        $this->global_file_slug = [];
        $this->global_file_value = [];
        $config_fields = $this->getConfigFile()->toArray();
        $scope = $request->scope ?? "global";

        $filter_config_fields = getDotToArray($request->absolute_path, $config_fields);

        return collect($filter_config_fields)->pluck('elements')->flatten(1)->map(function($data) {
            return $data;
        })->reject(function ($data) use($scope, $request) {
            if($data["type"] == "file") return $this->handleFileIssue($request, $data, $scope);
            return $this->scopeFilter($scope, $data["scope"]);
        })->mapWithKeys(function($item) use($scope) {
            $prefix = "items.{$item['path']}";
            $value_path =  "$prefix.value";
            $default_path =  "$prefix.use_default_value";
            $absolutePath = "$prefix.absolute_path";

            $value_rule = ($item["is_required"] == 1) ? (($scope == "global") ? "required" : "required_without:$default_path") : "nullable";
            $value_rule = "$value_rule|{$item["rules"]}";

            if($scope != "global") $default_rule = ($item["is_required"] == 1) ? "boolean|required_without:$value_path" : "boolean";

            $return_rules = [
                $value_path => $value_rule,
                $absolutePath => "required"
            ];

            if(($item["type"] == "select" && $item["multiple"]) || $item["type"] == "checkbox")
            {
                $child_rule = ($item["is_required"] == 1) ? "required_without:$default_path" : "nullable";
                $child_rule = "$child_rule|{$item["value_rules"]}";

                $return_rules = array_merge($return_rules, [
                    "{$value_path}.*" => $child_rule
                ]);
            }
            return ($scope != "global") ? array_merge($return_rules, [
                $default_path => $default_rule
            ]) : $return_rules
                ;
        })->toArray();
    }

    public function handleFileIssue(object $request, array $data, string $scope): bool
    {
        try
        {
            if (isset($request->items[$data["path"]])) {
                $request_slug = $request->items[$data["path"]];
                if (isset($request_slug["value"]) && !is_file($request_slug["value"])  && !isset($request_slug["use_default_value"])) {
                    $checkKey = [
                        "scope" => $scope,
                        "scope_id" => $request->scope_id ?? 0,
                        "path" => $data["path"]
                    ];
                    $exist_file = $this->checkCondition((object) $checkKey)->first();
                    if ($exist_file?->value && (Storage::url($exist_file?->value) == $request_slug["value"])) {
                        $this->global_file_slug[] = $data["path"];
                        return true;
                    }
                    else {
                        return $this->checkOnParentScope($checkKey, $request_slug["value"]);
                    }
                }
            }
            return false;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function checkOnParentScope(array $checkKey, mixed $value): bool
    {
        try
        {
            $bool = false;
            if($checkKey["scope"] != "global") {
                $input["path"] = $checkKey["path"];
                switch($checkKey["scope"])
                {
                    case "store":
                        $input["scope"] = "channel";
                        $input["scope_id"] = $this->store_model->find($checkKey["scope_id"])->channel->id;
                        break;

                    case "channel":
                        $input["scope"] = "website";
                        $input["scope_id"] = $this->channel_model->find($checkKey["scope_id"])->website->id;
                        break;

                    case "website":
                        $input["scope"] = "global";
                        $input["scope_id"] = 0;
                        break;
                }

                $exist_file = $this->checkCondition((object) $input)->first();
                if ($exist_file?->value && (Storage::url($exist_file?->value) == $value)) {
                    $this->global_file_value[$input["path"]] = $exist_file?->value;
                    $bool = true;
                }
                else return $this->checkOnParentScope($input, $value);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $bool;
    }

    public function add(object $request): object
    {
        $item['scope'] = $request->scope;
        $item['scope_id'] = $request->scope_id;
        $config_fields = $this->getConfigFile()->toArray();

        foreach($request->items as $key => $val)
        {
            if(is_array($this->global_file_slug) && in_array($key, $this->global_file_slug)) continue;

            if(isset($val["use_default_value"]) && $val["use_default_value"] != 1) throw ValidationException::withMessages([ "use_default_value" => __("core::app.response.use_default_value") ]);

            if(!isset($val["use_default_value"]) && !array_key_exists("value", $val)) throw ValidationException::withMessages([ "value" => __("core::app.response.value_missing", ["name" => $key]) ]);


            $configDataArray = getDotToArray($val["absolute_path"], $config_fields);
            if(!$configDataArray) throw ValidationException::withMessages([ "absolute_path" =>  __("core::app.response.absolute_path_not_exist", ["name" => $key]) ]);

            if($configDataArray["path"] != $key) throw ValidationException::withMessages([ "absolute_path" =>  __("core::app.response.wrong_absolute_path", ["name" => $key])]);

            if($this->scopeFilter($item['scope'], $configDataArray["scope"])) continue;

            $item['path'] = $key;
            $item['value'] = isset($val['value']) ? (($configDataArray["type"] == "file" ) ? $this->storeImage($val['value'], "configuration") : $val['value']) : null;

            if($configData = $this->checkCondition((object) $item)->first())
            {
                if(isset($val['use_default_value'])  && $val['use_default_value'] == 1) {
                    if(Redis::exists("configuration_data_{$item['scope']}_{$item['scope_id']}_{$item['path']}")) {
                        Redis::del("configuration_data_{$item['scope']}_{$item['scope_id']}_{$item['path']}");
                    }
                    $configData->delete();
                }
                else $created_data['data'][] = $this->update($item, $configData->id);
                continue;
            }
            if(isset($val['use_default_value'])  && $val['use_default_value'] == 1) continue;
            $created_data['data'][] = $this->create($item);
        }

        //parent image for child scope
        foreach($this->global_file_value as $file_key => $file_data) {
            $item["path"] = $file_key;
            $item['value'] = $file_data;
            $created_data['data'][] = $this->create($item);
        }
        $created_data['message'] = 'create-success';
        $created_data['code'] = 201;
        return (object) $created_data;
    }

    public function getValues(object $request): mixed
    {
        return $this->checkCondition($request)->first()?->value;
    }

    public function getDefaultValues(object $data, mixed $configValue=null): mixed
    {
        if($data->scope != "global")
        {
            $input["path"] = $data->path;
            switch($data->scope)
            {
                case "store":
                    $input["scope"] = "channel";
                    $input["scope_id"] = $this->store_model->find($data->scope_id)->channel->id;
                    break;

                case "channel":
                    $input["scope"] = "website";
                    $input["scope_id"] = $this->channel_model->find($data->scope_id)->website->id;
                    break;

                case "website":
                    $input["scope"] = "global";
                    $input["scope_id"] = 0;
                    break;
            }
            return ($item = $this->checkCondition((object) $input)->first()) ? $item->value : (( $input["scope"] == "global") ? $configValue : $this->getDefaultValues((object)$input, $configValue));
        }
        return $configValue;
    }

    public function storeImage(object $request, ?string $folder = null, ?string $delete_url = null): string
    {
        try
        {
            // Store File
            $file = $request;
            $key = Str::random(6);
            $folder = $folder ?? "default";
            $file_path = $file->storeAs("images/{$folder}/{$key}", $this->generateFileName($file));

            // Delete old file if requested
            if ( $delete_url !== null ) Storage::delete($delete_url);
        }
        catch (\Exception $exception)
        {
            throw $exception;
        }

        return $file_path;
    }

    public function getSinglePathValue($request): mixed
    {
        try
        {
            $config_fields = $this->getConfigFile();
            $elements = $config_fields->pluck("children")->flatten(1)->pluck("subChildren")->flatten(1)->pluck("elements")->flatten(1);
            $element = $elements->where("path", $request->path)->first();

            if(!$element) throw ValidationException::withMessages([ "path" => "Invalid Path" ]);

            $values = ($this->has((object) $request)) ? $this->getValues($request) : $this->getDefaultValues($request, $element["default"]);
            $fetched = ($values && $values != "" && $element["provider"] != "") ? $this->getProviderData($element, $values) : $values;
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getProviderData(array $element, mixed $values): array
    {
        try
        {
            $model = new $element["provider"];
            $fetched = is_array($values) ? $model->whereIn("id", $values)->get() : $model->find($values);
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $fetched->toArray();
    }

}
