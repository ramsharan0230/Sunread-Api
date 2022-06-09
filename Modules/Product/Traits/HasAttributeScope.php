<?php

namespace Modules\Product\Traits;

use Exception;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;

trait HasAttributeScope
{
    protected $non_filterable_fields = [ "select", "multiselect", "checkbox", "categoryselect" ];
    protected $attribute_val;

    public function value(array $data): mixed
    {
        try
        {
            if (isset($data["attribute_id"])) {
                $attribute_val = Attribute::with(["attribute_options"])->find($data["attribute_id"]);
            }

            if (isset($data["attribute_slug"])) {
                $attribute_val = Attribute::with(["attribute_options"])->whereSlug($data["attribute_slug"])->first();
                $data["attribute_id"] = $attribute_val->id;
                unset($data["attribute_slug"]);
            }

            $existAttributeData = $this->product_attributes
                ->where("scope", $data["scope"])
                ->where("scope_id", $data["scope_id"])
                ->where("attribute_id", $data["attribute_id"])
                ->first();

            $default = $existAttributeData ? $existAttributeData->value?->value : $this->getDefaultValues($data, $attribute_val);
            $fetched = is_json($default) ? json_decode($default) : $default;

            if (in_array( $attribute_val->type, $this->non_filterable_fields)) {
                $attribute_option_class = $attribute_val->getConfigOption() ? $attribute_val->getModelClass() : new AttributeOption();
                $fetched = is_array($fetched) ? $attribute_option_class->whereIn("id", $fetched)->get() : $attribute_option_class->find($fetched);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getDefaultValues(array $data, object $attribute): mixed
    {
        try
        {
            if (in_array($attribute->type, [ "multiselect", "checkbox" ])) {
                $attributeOptions = $attribute->attribute_options->where("is_default", 1)->pluck("id")->toArray();
                $attributeOptions = (count($attributeOptions) > 0 ) ? $attributeOptions : null;
            }
            if (in_array($attribute->type, [ "select" ])) {
                $attributeOptions = $attribute->attribute_options->where("is_default", 1)->first()?->id;
            }

            $defaultValue = isset($attributeOptions) ? $attributeOptions : $attribute->default_value;

            if ($data["scope"] != "website") {
                $parent_scope = $this->getParentScope($data);
                $data["scope"] = $parent_scope["scope"];
                $data["scope_id"] = $parent_scope["scope_id"];
            }
            $item = $this->product_attributes
                ->where("scope", $data["scope"])
                ->where("scope_id", $data["scope_id"])
                ->where("attribute_id", $data["attribute_id"])
                ->first();

            if ($item) {
                $default_value = $item->value?->value;
            } else {
                $default_value = ($data["scope"] != "website")
                    ? $this->getDefaultValues($data, $attribute)
                    : $defaultValue;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $default_value;
    }

    public function getBuilderParentValues(array $data): mixed
    {
        try
        {
            $data = $this->getParentScope($data);

            $builders = $this->productBuilderValues->where("scope", $data["scope"])->where("scope_id", $data["scope_id"])->sortBy("position");
            $fetched = ($builders->isEmpty() && ($data["scope"] != "website")) ? $this->getBuilderParentValues($data) : $builders;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getParentScope(array $data): array
    {
        try
        {
            switch ($data["scope"]) {
                case "store":
                    $data["scope"] = "channel";
                    $data["scope_id"] = Store::find($data["scope_id"])->channel->id;
                    break;

                case "channel":
                    $data["scope"] = "website";
                    $data["scope_id"] = Channel::find($data["scope_id"])->website->id;
                    break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }
}
