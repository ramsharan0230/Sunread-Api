<?php

namespace Modules\Product\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Product\Entities\ProductAttribute;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\Attribute\Entities\AttributeSet;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Tax\Entities\CustomerTaxGroup;
use Illuminate\Support\Str;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;
use Modules\Product\Entities\Feature;
use Modules\Product\Entities\ProductAttributeString;

class ProductAttributeRepository extends ProductRepository
{
    protected $option_fields, $attributeMapperSlug, $functionMapper, $product_repository, $non_option_slug, $non_required_attributes, $productBuilderRepository, $global_file_value;

    public function __construct(ProductAttribute $productAttribute, ProductRepository $product_repository, ProductBuilderRepository $productBuilderRepository)
    {
        $this->model = $productAttribute;
        $this->model_key = "catalog.products.attibutes";
        $this->product_repository = $product_repository;
        $this->productBuilderRepository = $productBuilderRepository;

        $this->option_fields = [ "select", "multiselect", "checkbox", "multiimages", "productimages" ];

        $this->attributeMapperSlug = [ "quantity_and_stock_status", "sku", "status", "category_ids", "gallery" ];
        $this->functionMapper = [
            "sku" => "sku",
            "status" => "status",
            "quantity_and_stock_status" => "catalogInventory",
            "category_ids" => "categories",
            "gallery" => "gallery",
        ];
        $this->non_required_attributes = [ "price", "cost", "special_price", "special_from_date", "special_to_date", "quantity_and_stock_status" ];
        $this->non_option_slug = [ "tax_class_id", "category_ids", "quantity_and_stock_status", "features" ];
    }

    public function attributeSetCache(): object
    {
        try
        {
            if (!Cache::has("attributes_attribute_set"))
            {
                Cache::remember("attributes_attribute_set", Carbon::now()->addDays(2) ,function () {
                    return AttributeSet::with([ "attribute_groups.attributes" ])->get();
                });
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return Cache::get("attributes_attribute_set");
    }

    public function validateAttributes(object $product, object $request, array $scope, ?string $method = null, ?string $product_type = null): array
    {
        try
        {
            $this->global_file_value = [];
            $attribute_set = AttributeSet::where("id", $product->attribute_set_id)->first();
            //$attribute_set = $this->attributeSetCache()->where("id", $product->attribute_set_id)->first();

            // get all the attributes of following attribute set
            $attributes = $attribute_set->attribute_groups->map(function($attributeGroup){
                return $attributeGroup->attributes;
            })->flatten(1);

            // get all request attribute id
            $request_attribute_slugs = array_map( function ($request_attribute) {
                if(!isset($request_attribute["attribute_slug"])) throw ValidationException::withMessages(["attribute_slug" => "Invalid attribute format."]);
                return $request_attribute["attribute_slug"];

            }, $request->get("attributes"));

            $request_attribute_collection = collect($request["attributes"]);

            if($product->parent_id) $configurable_attribute_ids = $product->parent->attribute_configurable_products->pluck("attribute_id")->toArray();

            $all_product_attributes = [];

            if($product_type) $super_attributes = Arr::pluck($request->super_attributes, 'attribute_slug');

            $validation_types = $this->getValidationTypes();

            foreach ( $attributes as $attribute )
            {
                $product_attribute = [];

                //removed some attributes in case of configurable products
                if($method == "update" && $product_type && in_array($attribute->slug, $this->non_required_attributes)) continue;

                //Super attribute filter in case of configurable products
                if(isset($super_attributes) && (in_array($attribute->slug, $super_attributes))) continue;

                //Skip if product is variant and attribute is configurable
                if($method == "update" && isset($configurable_attribute_ids) && in_array($attribute->id, $configurable_attribute_ids)) continue;

                $single_attribute_collection = $request_attribute_collection->where('attribute_slug', $attribute->slug);
                $default_value_exist = $single_attribute_collection->pluck("use_default_value")->first();

                $product_attribute["attribute_slug"] = $attribute->slug;
                if($default_value_exist == 1)
                {
                    $product_attribute["use_default_value"] = 1;
                    $all_product_attributes[] = $product_attribute;
                    continue;
                }

                $product_attribute["value"] = in_array($attribute->slug, $request_attribute_slugs) ? $single_attribute_collection->pluck("value")->first() : null;

                if($method == "update" && ($attribute->type == "image" || $attribute->type == "file") && isset($product_attribute["value"])) {
                    $bool_val = $this->handleFileIssue($product, $request, $attribute, $product_attribute["value"]);
                    if($bool_val) continue;
                }

                $attribute_type = config("attribute_types")[$attribute->type ?? "string"];

                $validation_messages = $this->getMessages($attribute, $validation_types);
                $validator = Validator::make($product_attribute, [
                    "value" => $attribute->type_validation
                ], $validation_messages);

                if ( $validator->fails() ) throw ValidationException::withMessages([$attribute->name => $validator->errors()->toArray()]);

                if(isset($product_attribute["value"]) && in_array($attribute->type, $this->option_fields)) $this->optionValidation($attribute, $product_attribute["value"]);

                if($attribute->slug == "quantity_and_stock_status") $product_attribute["catalog_inventory"] = $single_attribute_collection->pluck("catalog_inventory")->first();

                $all_product_attributes[] = array_merge($product_attribute, ["value_type" => $attribute_type], $validator->valid());
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        //parent image for child scope
        $all_product_attributes = array_merge($all_product_attributes, $this->global_file_value);
        return $all_product_attributes;
    }

    public function handleFileIssue(object $product, object $request, object $attribute, mixed $value): bool
    {
        try
        {
            $scope["scope"] = $request->scope ?? "website";
            $scope["scope_id"] = $request->scope_id ?? $product->website_id;

            if ($value && !is_file($value)) {
                $exist_file = $product->product_attributes()->whereAttributeId($attribute->id)->whereScope($scope["scope"])->whereScopeId($scope["scope_id"])->first();
                if ($exist_file?->value?->value && (Storage::url($exist_file?->value?->value) == $value)) {
                    return true;
                }
                else {
                    return $this->checkOnParentScope($product, $scope, $attribute, $value);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return false;
    }

    public function checkOnParentScope(object $product, array $scope, object $attribute, mixed $value): bool
    {
        try
        {
            $bool = false;
            if($scope["scope"] != "website") {
                switch($scope["scope"])
                {
                    case "store":
                        $input["scope"] = "channel";
                        $input["scope_id"] = Store::find($scope["scope_id"])->channel->id;
                        break;

                    case "channel":
                        $input["scope"] = "website";
                        $input["scope_id"] = Channel::find($scope["scope_id"])->website->id;
                        break;
                }

                $exist_file = $product->product_attributes()->whereAttributeId($attribute->id)->whereScope($input["scope"])->whereScopeId($input["scope_id"])->first();

                if ($exist_file?->value?->value && (Storage::url($exist_file?->value?->value) == $value)) {
                    $file_value = [
                        "attribute_slug" => $attribute->slug,
                        "value" => $exist_file?->value?->value,
                        "value_type" => "Modules\Product\Entities\ProductAttributeText"
                    ];
                    $this->global_file_value[] = $file_value;
                    $bool = true;
                }
                else return $this->checkOnParentScope($product, $input, $attribute, $value);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $bool;
    }

    public function optionValidation(object $attribute, mixed $values): void
    {
        if(in_array($attribute->type, ["checkbox", "multiselect"])) $this->multipleOptionValidation($attribute, $values);
        if(in_array($attribute->type, ["select"])) $this->singleOptionValidation($attribute, $values);
        //if(in_array($attribute->type, ["multiimages"])) $this->multipleImageValidation($attribute, $values);
    }

    public function singleOptionValidation(object $attribute, mixed $values): void
    {
        if(in_array($attribute->slug, $this->non_option_slug))
        {
            if($attribute->slug == "tax_class_id") {
                $attribute_options = CustomerTaxGroup::pluck("id")->toArray();
                $attribute_options[] = 0;
            }
            if($attribute->slug == "features") $attribute_options = Feature::pluck("id")->toArray();

            if($attribute->slug == "quantity_and_stock_status") $attribute_options = [ 0 => 0, 1 => 1];
        }
        else $attribute_options = AttributeOption::whereAttributeId($attribute->id)->pluck("id")->toArray();

        if(isset($attribute_options) && !in_array($values, $attribute_options)) throw ValidationException::withMessages([$attribute->name => "Invalid Attribute option"]);

    }

    public function multipleOptionValidation(object $attribute, mixed $values): void
    {
        foreach($values as $value)
        {
            $this->singleOptionValidation($attribute, $value);
        }
    }

    public function multipleImageValidation(object $attribute, mixed $values): void
    {
        try
        {
            foreach($values as $value)
            {
                if(is_file($value)) {
                    $images["value"] = $value;
                    $validator = Validator::make($images, [
                        "value" => "mimes:bmp,jpeg,jpg,png"
                    ]);
                    if ( $validator->fails() ) throw ValidationException::withMessages([$attribute->name => $validator->errors()->toArray()]);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function syncAttributes(array $data, object $product, array $scope, object $request, string $method = "store", ?string $product_type = null): bool
    {
        DB::beginTransaction();
        Event::dispatch("{$this->model_key}.sync.before");

        try
        {
            foreach($data as $attribute) {

                $scope_arr = $scope;

                //removed some attributes in case of configurable products
                if($product_type && in_array($attribute['attribute_slug'], $this->non_required_attributes)) continue;

                $db_attribute = Attribute::whereSlug($attribute['attribute_slug'])->first();

                if( in_array($attribute["attribute_slug"], $this->attributeMapperSlug) )
                {
                    /**
                     * enable or disable channel products
                    */
                    if($attribute["attribute_slug"] == "status" && $scope_arr["scope"] != "website") {
                        $this->changeStatus($attribute, $product, $scope_arr, $db_attribute);
                        continue;
                    }
                    // store mapped attributes on respective function. ( sku, categories.)
                    $function_name = $this->functionMapper[$attribute["attribute_slug"]];
                    $this->product_repository->$function_name($product, $request, $method, $attribute, $product_type);
                    continue;
                }

                if($attribute["attribute_slug"] == 'component'){
                    $this->productBuilderRepository->component($product, $method, $attribute, $scope_arr);
                    continue;
                }

                if($this->product_repository->scopeFilter($scope_arr["scope"], $db_attribute->scope)) $scope_arr = $this->product_repository->getParentScopeWithFilter($scope_arr, $db_attribute->scope);

                $match = [
                    "product_id" => $product->id,
                    "scope" => $scope_arr["scope"],
                    "scope_id" => $scope_arr["scope_id"],
                    "attribute_id" => $db_attribute->id
                ];

                if((isset($attribute["use_default_value"]) && $attribute["use_default_value"] == 1) || !isset($attribute["value"]))
                {
                    $product_attribute = ProductAttribute::where($match)->first();
                    if($product_attribute) $product_attribute->delete();
                    continue;
                }

                if(is_array($attribute["value"])) {
                    if($db_attribute->type == "multiimages") $attribute["value"] = $this->handleMultiImages($attribute["value"], $match);
                    else $attribute["value"] = json_encode($attribute["value"], JSON_NUMERIC_CHECK);
                }
                if(is_file($attribute["value"])) $attribute["value"] = $this->product_repository->storeScopeImage($attribute["value"], "product");
                if($db_attribute->slug == "url_key") $attribute["value"] = $this->createUniqueSlug($request, $product, $data, Str::slug($attribute["value"]));

                $product_attribute = ProductAttribute::updateOrCreate($match, $attribute);

                if ( $product_attribute->value_id != null ) {
                    $product_attribute->value()->each(function($attribute_value) use($attribute){
                        $attribute_value->update(["value" => $attribute["value"]]);
                    });
                    continue;
                }
                // store attribute value on attribute type table
                $product_attribute->update(["value_id" => $attribute["value_type"]::create(["value" => $attribute["value"]])->id]);
            }
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        if(isset($product_attribute)) Event::dispatch("{$this->model_key}.sync.after", $product_attribute);
        DB::commit();

        return true;
    }

    public function handleMultiImages(array $values, array $match): string
    {
        try
        {
            $img_data = [];
            $url_word_count = strlen(url("/storage"));

            $exist_images = ProductAttribute::where($match)->first()?->value?->value;
            if($exist_images) $exist_values = json_decode($exist_images);

            foreach($values as $val)
            {
                if(is_file($val)) $img_data[] = $this->product_repository->storeScopeImage($val, "product");
                else {
                    $final_val = substr($val, ($url_word_count+1));
                    //if(isset($exist_values) && !in_array($final_val, $exist_values)) continue;
                    $img_data[] = $final_val;
                }
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return json_encode($img_data);
    }

    public function createUniqueSlug(object $request, object $product, array $collection, ?string $slug): string
    {
        try
        {
            if(!$slug) {
                $name = collect($collection)->where('attribute_slug', "name")->pluck("value")->first();
                $slug = Str::slug($name);
            }
            $original_slug = $slug;

            $count = 1;

            while ($this->checkSlug($request, $product, $slug)) {
                $slug = "{$original_slug}-{$count}";
                $count++;
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $slug;
    }

    public function checkSlug(object $request, object $product, ?string $slug): ?object
    {
        try
        {
            $url_key_id = optional(Attribute::whereSlug("url_key")->first())->id;
            $data = $this->model->where('product_id', '!=', $product->id)->whereHas("product", function ($query) use ($request) {
                $query->whereWebsiteId($request->website_id);
            })->whereAttributeId($url_key_id)->whereHasMorph("value", [ProductAttributeString::class], function ($query) use ($slug) {
                $query->whereValue($slug);
            })->first();
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function changeStatus(array $value, object $product, array $scope, object $attribute): bool
    {
        try
        {
            if($scope["scope"] == "store") $scope["scope_id"] = Store::find($scope["scope_id"])?->channel_id;

            $attribute_option = AttributeOption::whereAttributeId($attribute->id)->whereCode(0)->first();

            if($value["value"] == $attribute_option->id) $product->channels()->sync($scope["scope_id"], false);
            else $product->channels()->detach($scope["scope_id"]);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return true;
    }

    public function getMessages(object $attribute, array $types): array
    {
        try
        {
            foreach ($types as $type) {
                $messages[$type] = __("core::app.validation.{$type}", ["name" => $attribute->name]);
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $messages;
    }

    public function getValidationTypes(): array
    {
        try
        {
            $types = [
                "decimal",
                "integer",
                "array",
                "date",
                "file",
                "boolean",
                "string",
                "required",
                "max:25",
                "mimes",
                "url",
                "exists",
                "max",
            ];
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $types;
    }
}
