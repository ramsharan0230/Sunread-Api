<?php

namespace Modules\Attribute\Repositories;

use Exception;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Exceptions\AttributeCannotChangeException;
use Modules\Attribute\Exceptions\AttributeTranslationDoesNotExist;
use Modules\Attribute\Exceptions\AttributeTranslationOptionDoesNotExist;
use Modules\Core\Repositories\BaseRepository;

class AttributeRepository extends BaseRepository
{
    public $attribute_types, $non_filterable_fields;

    public function __construct(Attribute $attribute)
    {
        $this->model = $attribute;
        $this->model_key = "catalog.attribute";

        $this->non_filterable_fields = [ "select", "multiselect", "checkbox", "categoryselect"];
        $this->non_updatable_fields_for_system = [ "slug", "type", "scope", "is_unique", "is_searchable", "search_weight", "is_visible_on_storefront", "use_in_layered_navigation", "position", "comparable_on_storefront" ];
        $this->non_updatable_fields_for_user = [ "slug", "type" ];

        $attribute_types = implode(",", array_keys(config("attribute_types")));
        $this->rules = [
            "slug" => "nullable|unique:attributes,slug",
            "name" => "required",
            "type" => "required|in:{$attribute_types}",
            "scope" => "required|in:website,channel,store",
            "is_required" => "sometimes|boolean",
            "comparable_on_storefront" => "sometimes|boolean",
            "validation" => "nullable|in:decimal,integer,url,email",
            "is_visible_on_storefront" => "sometimes|boolean",
            "use_in_layered_navigation" => "sometimes|boolean",
            "position" => "required_if:use_in_layered_navigation,==,1|nullable|numeric",
            "is_searchable" => "sometimes|boolean",
            "is_unique" => "sometimes|boolean",
            "search_weight" => "required_if:is_searchable,==,1|nullable|numeric",
            "translations" => "nullable|array",
            "is_synchronized" => "sometimes|boolean"
        ];
    }

    public function validateTranslationData(?array $translations): bool
    {
        // if (empty($translations)) return false;

        foreach ($translations as $translation) {
            if (!array_key_exists("store_id", $translation) || !array_key_exists("name", $translation)) return false;
        }

        return true;
    }

    public function validateTranslation(object $request): void
    {
        $translations = $request->translations;
        if (!$this->validateTranslationData($translations)) {
            throw new AttributeTranslationDoesNotExist(__("core::app.response.missing-data", ["name" => "Attribute"]));
        }

        $options = $request->attribute_options;
        if (is_array($options) && in_array($request->type, $this->non_filterable_fields)) {
            foreach ($options as $option) {
                if (!isset($option["translations"]) || !$this->validateTranslationData($option["translations"])) {
                    throw new AttributeTranslationOptionDoesNotExist(__("core::app.response.missing-data", ["name" => "Attribute Option"]));
                }
            }
        }
    }

    public function validateFieldOnUpdate(array $data, int $id): void
    {
        try
        {
            $model_data = $this->model->findOrFail($id);
            $type = ($model_data->is_user_defined) ?  "user" : "system";
            $key = "non_updatable_fields_for_$type" ;
            
            foreach($this->$key as $field){
                if(isset($data[$field]) && $data[$field] != $model_data->$field) throw new AttributeCannotChangeException(__("core::app.response.attribute-cannot-change", [ "field" => $field, "type" => $type ]));
            }
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
    }

    public function preparingData(object $request, array $data): array
    {
        try
        {
            if (!in_array($request->type, $this->non_filterable_fields)) {
                $data["use_in_layered_navigation"] = 0;
            } else {
                $data["default_value"] = null;
            }

            if (!isset($data["is_synchronized"])) {
                $data["is_synchronized"] = 1;
            }
            
            if ($request->type != "text") {
                $data["validation"] = null;
            }
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $data;
    }
}
