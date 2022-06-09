<?php

namespace Modules\Attribute\Repositories;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeSet;
use Modules\Core\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;
use Modules\Inventory\Entities\CatalogInventory;

class AttributeSetRepository extends BaseRepository
{
    public $attribute_repository, $attribute_model;

    public function __construct(AttributeSet $attribute_set, AttributeRepository $attribute_repository, Attribute $attribute_model)
    {
        $this->model = $attribute_set;
        $this->attribute_model = $attribute_model;
        $this->model_key = "catalog.attributes.attribute_set";
        $this->attribute_repository = $attribute_repository;

        $this->rules = [
            "name" => "required"
        ];
    }

    public function attributeValidation(array $data)
    {
        $attribute_ids_array = Arr::flatten(Arr::pluck($data["groups"], 'attributes'));

        if(count($attribute_ids_array) > count(array_unique($attribute_ids_array))) 
        throw ValidationException::withMessages(["attributes" => "Different attribute groups consisting of same attributes."]);

        $default_attribute_ids = Attribute::whereIsUserDefined(0)->pluck('id')->toArray();
        if(array_diff($default_attribute_ids, $attribute_ids_array)) throw ValidationException::withMessages(["attributes" => "Default attributes are missing."]);
    }

    // public function validateAttributeSetListing(object $request): array
    // {
    //     return $request->validate([
    //         "product" => ($request->get("product")) ? "required|integer|exists:products,id" : "nullable",
    //         "attribute_set" => ($request->get("product")) ? "nullable" : "required|integer"
    //     ]); 
    // }

    public function generateFormat(int $id): array
    {
        try
        {
            $data = $this->model->findOrFail($id);
    
            $groups = $data->attribute_groups->sortBy("position")->map(function ($attribute_group) { 
                return [
                    "id" => $attribute_group->id,
                    "name" => $attribute_group->name,
                    "position" => $attribute_group->position,
                    "attributes" => $attribute_group->attributes->sortBy("pivot.position")->map(function ($attribute) {
                        $attributesData = [
                            "id" => $attribute->id,
                            "name" => $attribute->name,
                            "slug" => $attribute->slug,
                            "type" => $attribute->type,
                            "scope" => $attribute->scope,
                            "position" => $attribute->position,
                            "is_required" => $attribute->is_required,
                            "value" => $attribute->default_value,
                            "is_user_defined" => (bool) $attribute->is_user_defined,
                            "is_synchronized" => (bool) $attribute->is_synchronized
                        ];

                        if(in_array($attribute->type, $this->attribute_repository->non_filterable_fields))
                        {
                            $attributesData["options"] = $this->getAttributeOption($attribute);

                            if(in_array($attribute->type, [ "multiselect", "checkbox" ])) {
                                $attributeDefault = $attribute->attribute_options()->whereIsDefault(1)->pluck("id")->toArray();
                                $attributesData["value"] = (count($attributeDefault) > 0 ) ? $attributeDefault : null;
                            }
                            else $attributesData["value"] = $attribute->attribute_options()->whereIsDefault(1)->first()?->id;
                        } 
                        if($attribute->slug == "quantity_and_stock_status") $attributesData["children"] = $this->getInventoryChildren();

                        return $attributesData;
                    })->values()->toArray()
                ];
            })->values()->toArray();
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
        return [
            "attribute_set_id" => $id,
            "groups" => $groups
        ];
    }

    public function getAttributeOption(object $attribute): array
    {
        try
        {
            $configOptions = $attribute->getConfigOption();

            $options = $configOptions ?? $attribute->attribute_options;
            $attribute_options = $options->map( function ($option) use ($attribute) {

                $data_options = [
                    "value" => $option->id,
                    "label" => $option->name
                ];
                if($attribute->slug == "features") {
                    $data_options["icon"] = $option->image ? Storage::url($option->image) : null;
                }
                return $data_options;
            })->toArray();

            if($attribute->slug == "tax_class_id") {
                $attribute_options[] = [
                    "value" => 0,
                    "label" => "None"
                ];
            }
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $attribute_options;
    }

    public function getInventoryChildren(?int $id = null): array
    {
        try
        {
            $inventory = $id ? CatalogInventory::whereProductId($id)->first() : null;
            
            $children["catalog_inventory"] = [
                [
                    "name" => "Quantity",
                    "slug" => "quantity",
                    "type" => "number",
                    "value" => $inventory?->quantity
                ],
                [
                    "name" => "Use Config Manage Stock",
                    "slug" => "use_config_manage_stock",
                    "type" => "select",
                    "options" => [
                        [
                            "value" => 1,
                            "label" => "Yes"
                        ],
                        [
                            "value" => 0,
                            "label" => "No"
                        ]
                    ],
                    "value" => $inventory?->use_config_manage_stock
                ],
                [
                    "name" => "Manage Stock",
                    "slug" => "manage_stock",
                    "type" => "select",
                    "options" => [
                        [
                            "value" => 1,
                            "label" => "Yes"
                        ],
                        [
                            "value" => 0,
                            "label" => "No"
                        ]
                    ],
                    "value" => $inventory?->manage_stock
                ],
            ];
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $children;
    }
}
