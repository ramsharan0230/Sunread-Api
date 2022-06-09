<?php

namespace Modules\Product\Repositories;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Product\Entities\Product;
use Modules\Attribute\Entities\Attribute;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Core\Repositories\BaseRepository;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Exceptions\SlugCouldNotBeGenerated;
use Modules\Product\Entities\AttributeConfigurableProduct;
use Modules\Product\Entities\AttributeOptionsChildProduct;
use Modules\Product\Entities\ProductAttribute;
use Modules\Product\Entities\ProductAttributeString;

class ProductConfigurableRepository extends BaseRepository
{
    protected $attribute; 
    protected $product_repository;
    protected array $non_required_attributes;
    protected $product_attribute_repository;
    protected array $configurable_attribute_options;
    protected array $state;
    protected $group_by_attribute;

    public function __construct(
        Product $product, 
        Attribute $attribute, 
        ProductRepository $product_repository, 
        ProductAttributeRepository $productAttributeRepository
    ) {
        $this->model = $product;
        $this->model_key = "catalog.products";
        $this->rules = [
            "brand_id" => "sometimes|nullable|exists:brands,id",
            "super_attributes" => "required|array",
            "attributes" => "required|array",
            "scope" => "sometimes|in:website,channel,store",
            "grouping_attributes" => "sometimes|exists:attributes,slug",
            "update_attributes" => "required_with:update_variants|array",
            "update_attributes.*" => "exists:attributes,slug",
            "update_variants" => "array|required_with:update_attributes",
            "update_variants.*" => "exists:products,id"
        ];
        $this->attribute = $attribute;
        $this->product_repository = $product_repository;
        $this->product_attribute_repository = $productAttributeRepository;
        $this->non_required_attributes = [ "price", "cost", "quantity_and_stock_status" ];
        $this->asd = $this->attributeCache();
    }

    public function attributeCache(): Collection
    {
        try
        {
            if (!Cache::has("attributes"))
            {
                Cache::remember("attributes", Carbon::now()->addDays(2) ,function () {
                    return Attribute::with([ "attribute_options" ])->get();
                });
            }
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return Cache::get("attributes");
    }

    public function attributeOptionsCache(): Collection
    {
        try
        {
            if (!Cache::has("attribute_options"))
            {
                Cache::remember("attribute_options", Carbon::now()->addDays(2) ,function () {
                    return AttributeOption::with([ "attribute" ])->get();
                });
            }
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return Cache::get("attribute_options");
    }

    public function createVariants(
        object $product, 
        object $request, 
        array $scope, 
        array $request_attributes, 
        ?string $method = null
    ): bool {
       try
       {    
            //create product-superattribute
            $super_attributes = [];
            $this->configurable_attribute_options = [];
            $attribute_configurable_product_ids = [];

            $super_attribute_arr = Arr::pluck($request->get("super_attributes"), "attribute_slug");
            if ($request->grouping_attribute && !in_array($request->grouping_attribute, $super_attribute_arr)) {
                throw ValidationException::withMessages(["grouping_attribute" => "Grouping attribute should be one of the configurable attributes"]);
            }
            
            foreach ($request->get("super_attributes") as $super_attribute) {
                $attribute = Attribute::with([ "attribute_options" ])->whereSlug($super_attribute['attribute_slug'])->firstOrFail();
                if ($attribute->is_user_defined == 0 && $attribute->type != "select") continue;
                foreach ($super_attribute["value"] as $super_val) {
                    $this->product_attribute_repository->singleOptionValidation($attribute, $super_val);
                }
                $super_attributes[$attribute->id] = $super_attribute["value"];

                $parent_attributes = [
                    "product_id" => $product->id,
                    "attribute_id" => $attribute->id
                ]; 
                $grp_attribute["used_in_grouping"] = ($request->grouping_attribute == $attribute->slug) ? 1 : 0;
                $attribute_configurable_product = AttributeConfigurableProduct::updateOrCreate($parent_attributes, $grp_attribute); 
                if ($attribute_configurable_product) {
                    $attribute_configurable_product_ids[] = $attribute_configurable_product->id;
                }
            }

            $product->attribute_configurable_products()->whereNotIn('id', $attribute_configurable_product_ids)->delete();
            
            $this->parentVisibilitySetup($product, $scope);

            $productAttributes = collect($request_attributes)->reject(function ($item) {
                return (($item["attribute_slug"] == "sku") || ($item["attribute_slug"] == "visibility") || ($item["attribute_slug"] == "url_key"));
            })->toArray();

            $this->state = [];
            //generate multiple product(variant) combination on the basis of color, size (super_attributes/user defined attributes) for variants
            foreach (array_permutation($super_attributes) as $permutation) {
                $product_variant_data[] = $this->addVariant($product, $permutation, $request, $productAttributes, $scope, $method);
            }
            
            $product->variants()->whereNotIn('id', array_filter(Arr::pluck($product_variant_data, 'id')))->get()->map(function($single_product) {
                $this->product_repository->delete($single_product->id);
            });
       }
       catch ( Exception $exception )
       {
           throw $exception;
       }
       
       return true;
    }

    private function addVariant(
        object $product, 
        mixed $permutation, 
        object $request, 
        array $productAttributes, 
        array $scope, 
        ?string $method
    ): object {
        DB::beginTransaction();
        Event::dispatch("{$this->model_key}.attibutes.sync.before");

        try 
        {
            $permutation_modify = AttributeOption::with([ "attribute" ])->whereIn('id', $permutation)->pluck('name')->toArray();
            $data = [
                "parent_id" => $product->id,
                "website_id" => $product->website_id,
                "brand_id" => $product->brand_id,
                "type" => "simple",
                "attribute_set_id" => $product->attribute_set_id
            ];

            $product_attributes = [];
            $this->configurable_attribute_options = [];
            $variant_options = collect($permutation)->map(function ($option, $key) {
                $this->configurable_attribute_options[] = $option;
                $att_data = Attribute::find($key);
                return [
                    "attribute_slug" => $att_data->slug,
                    "value" => $option,
                    "value_type" => config("attribute_types")[($att_data->type) ?? "string"]
                ];
            })->toArray();

            if ($method && $method == "update") {
                $child_variant = $this->checkVariant($product);
            }

            /**
             * 
             * create variant simple product 
             */
            if (isset($child_variant)) {
                $product_variant = $this->update($data, $child_variant->id, function ($variant) use ($request, $productAttributes, $scope) {
                    if ($request->update_variants && in_array($variant->id, $request->update_variants)) {
                        $update_productAttributes = collect($productAttributes)->whereIn("attribute_slug", $request->update_attributes)->toArray();
                        $this->syncAttributesWithEvents($update_productAttributes, $variant, $scope, $request, "store");
                    }
                });
            } else {
                $product_variant = $this->create($data, function ($variant) use ($product, $permutation_modify, $request, &$product_attributes, $productAttributes, $scope, $variant_options) {
                    $this->syncConfigurableAttributes($product, $permutation_modify, $request, $product_attributes, $productAttributes, $scope, $variant_options, $variant);
                });
            }
        }
        catch ( Exception $exception )
        {
            DB::rollBack();
            throw $exception;
        }
        
        Event::dispatch("{$this->model_key}.attibutes.sync.after", $product_variant);
        DB::commit();

        return $product_variant;
    }

    private function checkVariant(object $product): ?object
    {
        try 
        {
            foreach ($product->variants as $child_variant) {
                    $exist_variant = $child_variant->attribute_options_child_products()
                        ->whereIn("attribute_option_id", $this->configurable_attribute_options)
                        ->get();

                    if ($exist_variant && count($exist_variant) == count($this->configurable_attribute_options)) {
                        $data = $child_variant;
                        break;
                    }
            }
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return $data ?? null;
    }

    private function syncConfigurableAttributes(
        object $product, 
        array $permutation_modify, 
        object $request, 
        array &$product_attributes, 
        array $productAttributes, 
        array $scope, 
        array $variant_options, 
        object $variant
    ): void {
        try 
        {
            $visibility = $this->childVisibilitySetup($variant_options, $variant);
            $name = collect($productAttributes)->where("attribute_slug", "name")->first();
            $erp_code = $this->getErpVariantCode();

            $product_attributes = array_merge([
                [
                    //Attribute slug
                    "attribute_slug" => "sku",
                    "value" => Str::slug($product->sku)."_".implode("_", $permutation_modify),
                    "value_type" => "Modules\Product\Entities\ProductAttributeString"
                ],
                [
                    //Attribute Url Key
                    "attribute_slug" => "url_key",
                    "value" => $this->createSlug(Str::slug($name["value"])),
                    "value_type" => "Modules\Product\Entities\ProductAttributeString"
                ],
            ], $productAttributes, $variant_options, [ $visibility, $erp_code ]);

            $this->product_attribute_repository->syncAttributes($product_attributes, $variant, $scope, $request, "store");

            array_map(function($child_attribute_option) use($variant) {
                AttributeOptionsChildProduct::updateOrCreate([
                    "attribute_option_id" => $child_attribute_option,
                    "product_id" => $variant->id 
                ]);
            }, $this->configurable_attribute_options);

            $variant->channels()->sync($request->get("channels"));
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }
    }

    private function childVisibilitySetup(array $variant_options, object $variant): array
    {
        try 
        {
            if ($this->group_by_attribute) {
                $group_by_option = $variant_options[$this->group_by_attribute->attribute_id]["value"];
            }

            if (isset($group_by_option)) {
                if (!isset($this->state[$group_by_option])) {
                    $this->state[$group_by_option] = $variant;
                    $visibility = $this->getVisibility("catalog_search");
                } else {
                    $visibility = $this->getVisibility("not_visible");
                }
            } else {
                $visibility = $this->getVisibility("not_visible"); 
            }
        }
        catch ( Exception $exception )
        {
            throw $exception;
        } 
        
        return $visibility;
    }

    private function parentVisibilitySetup(object $product, array $scope): void
    {
        try 
        {
            $this->group_by_attribute = AttributeConfigurableProduct::whereProductId($product->id)->whereUsedInGrouping(1)->first(); 

            $parent_product_attributes =  ProductAttribute::where(array_merge($scope, [
                "product_id" => $product->id,
                "attribute_id" => 11
            ]))->first();

            if ($parent_product_attributes) {
                $product_attribute_string = ProductAttributeString::find($parent_product_attributes->value_id);

                if ($this->group_by_attribute) {
                    $product_attribute_string->update(["value" => $this->getVisibilityId("not_visible")]);
                } else {
                    $product_attribute_string->update(["value" => $this->getVisibilityId("catalog_search")]);
                }
            }
        }
        catch ( Exception $exception )
        {
            throw $exception;
        } 
    }

    private function getVisibilityId(string $code): int
    {
        try 
        {
            $visibility_att = Attribute::whereSlug("visibility")->first();
            $attribute_option = AttributeOption::whereAttributeId($visibility_att->id)->whereCode($code)->first();
        }
        catch ( Exception $exception )
        {
            throw $exception;
        } 
        return $attribute_option->id;
    }

    private function getVisibility(string $code): array
    {
        try 
        {
            $visibility =  [
                "attribute_slug" => "visibility",
                "value" => $this->getVisibilityId($code),
                "value_type" => "Modules\Product\Entities\ProductAttributeString"
            ];
        }
        catch ( Exception $exception )
        {
            throw $exception;
        } 

        return $visibility;
    }

    public function createSlug(string $title, int $id = 0): string
    {
       try
       {
            // Slugify
            $slug = Str::slug($title);
            $original_slug = $slug;

            // Throw Error if slug could not be generated
            if ($slug == "") {
                throw new SlugCouldNotBeGenerated();
            }

            // Get any that could possibly be related.
            // This cuts the queries down by doing it once.
            $allSlugs = $this->getRelatedSlugs($slug, $id);

            // If we haven't used it before then we are all good.
            if (!$allSlugs->contains('value', $slug)) {
                return $slug;
            }

            //if used,then count them
            $count = $allSlugs->count();

            // Loop through generated slugs
            while ($this->checkIfSlugExist($slug, $id) && $slug != "") {
                $slug = "{$original_slug}-{$count}";
                $count++;
            }
       }
       catch ( Exception $exception )
       {
           throw $exception;
       }

        // Finally return Slug
        return $slug;
    }

    private function getRelatedSlugs(string $slug, int $id = 0): object
    {
        return ProductAttributeString::whereRaw("value RLIKE '^{$slug}(-[0-9]+)?$'")
            ->where('id', '<>', $id)
            ->get();
    }

    private function checkIfSlugExist(string $slug, int $id = 0): ?bool
    {
        return ProductAttributeString::select('value')->where('value', $slug)
            ->where('id', '<>', $id)
            ->exists();
    }

    public function updateVariants(array $data, object $request, object $updated, array $scope, array $attributes): void
    {
        try
        {
           if ($data["update_configurable_attributes"] == 1) {
               $this->createVariants($updated, $request, $scope, $attributes, "update");
           } else {
                if ($request->update_variants && $request->update_attributes) {
                    $update_productAttributes = collect($attributes)->whereIn("attribute_slug", $request->update_attributes)->toArray();
                    foreach ($request->update_variants as $variant_id) {
                        $variant = Product::find($variant_id);
                        if ($variant) {
                            $this->syncAttributesWithEvents($update_productAttributes, $variant, $scope, $request, "store");
                        }
                    }
                }
            }
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }
    }

    private function syncAttributesWithEvents(
        array $product_attributes, 
        object $product, 
        array $scope, 
        object $request, 
        string $method
    ): void {
        Event::dispatch("{$this->model_key}.update.before", $product->id);
        
        try 
        {
            $this->product_attribute_repository->syncAttributes($product_attributes, $product, $scope, $request, $method);    
        }
        catch ( Exception $exception )
        {
            throw $exception;
        } 

        Event::dispatch("{$this->model_key}.update.after", $product);
    }

    private function getErpVariantCode(): array
    {
        try 
        {
            $erp_codes = [];
            $erp_variant_code = "";
            foreach ($this->configurable_attribute_options as $j => $option_id) {
                $option = AttributeOption::find($option_id);

                if ($option->attribute->slug == "color") {   
                    $color_code = $option->code ?? $option->name;
                    $erp_variant_code = "{$color_code}{$erp_variant_code}";
                } else {
                    $erp_variant_code .= $option->name;
                }

                if (($j+1) == count($this->configurable_attribute_options)) {
                    $erp_codes = [
                        "attribute_slug" => "erp_variant_code",
                        "value" => $erp_variant_code,
                        "value_type" => "Modules\Product\Entities\ProductAttributeString"
                    ];
                }
            }
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return $erp_codes;
    }
}
