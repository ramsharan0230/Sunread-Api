<?php

namespace Modules\Product\Tests\Feature;

use Illuminate\Support\Arr;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Tests\BaseTestCase;
use Modules\Core\Entities\Website;
use Modules\Product\Entities\Product;
use Modules\Tax\Entities\CustomerTaxGroup;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Modules\Product\Entities\Feature;

class ProductConfigurableTest extends BaseTestCase
{
    public $default_resource;
    public function setUp(): void
    {
        $this->model = Product::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Product";
        $this->route_prefix = "admin.catalog.configurable-products";
        $this->default_resource = $this->model::latest('id')->first();
        $this->default_resource_id = $this->default_resource->id;
        $this->hasFilters = false;
        $this->hasIndexTest = false;
        $this->hasShowTest = false;
        $this->hasDestroyTest = false;
        $this->hasBulkDestroyTest = false;
        $this->hasStatusTest = false;
    }

    public function getCreateData(): array
    {
        $product = $this->model::factory()->make();
        $merge_product = $product->toArray();

        $attributes = [];

        foreach ( $product->attribute_set->attribute_groups as $attribute_group )
        {
            foreach ($attribute_group->attributes as $attribute)
            {
                if (in_array($attribute->slug, ["category_ids", "gallery", "quantity_and_stock_status", "component"])) continue;
                $attributes[] = [
                    "attribute_slug" => $attribute->slug,
                    "value" => $this->value($attribute)
                ];
            }
        }

        $super_attributes = Attribute::inRandomOrder()->whereisUserDefined(1)->whereType("select")->where("slug", "!=", "tax_class_id")->take(2)->get();

        foreach($super_attributes as $super_attribute)
        {
            $variant_attributes[] = [
                "attribute_slug" => $super_attribute->slug,
                "value" => $super_attribute->attribute_options->take(2)->pluck('id')->toArray()
            ];
        }

        return array_merge($merge_product, ["attributes" => $attributes], ["super_attributes" => $variant_attributes]);
    }

    public function getUpdateData(): array
    {
        $websiteId = $this->default_resource->website_id;
        $updateData = $this->getCreateData();
        return array_merge($updateData, $this->getScope($websiteId), ["update_configurable_attributes" => Arr::random([1,0])]);
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "parent_id" => null,
            "brand_id" => null
        ]);
    }

    public function getNonMandatoryUpdateData(): array
    {
        return array_merge($this->getUpdateData(), [
            "parent_id" => null,
            "brand_id" => null
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "attribute_set_id" => null
        ]);
    }

    public function testShouldReturnErrorIfUpdateDataIsInvalid()
    {
        $this->markTestSkipped("Invalid Update method not available.");
    }

    public function value(object $attribute): mixed
    {
        switch($attribute->type)
        {
            case "price" :
                $value = 1000.1;
                break;

            case "boolean" :
                $value = true;
                break;

            case ($attribute->type == "datetime" || $attribute->type == "date"):
                $value = now();
                break;

            case "number":
                $value = rand(1,1000);
                break;

            case "select" :
                $attribute_option = ($attribute->slug == "tax_class_id") ? CustomerTaxGroup::inRandomOrder()->first() : AttributeOption::create([
                    "attribute_id" => $attribute->id,
                    "name" => Str::random(10),
                    "code" => ($attribute->slug == "status") ? rand(0,1) : Str::random(10)
                ]);
                $value = $attribute_option->id;
                break;

            case ($attribute->type == "multiselect" || $attribute->type == "checkbox"):
                if ($attribute->slug == "features") {
                    $attribute_option = Feature::first();
                } else {
                    $attribute_option = AttributeOption::create([
                        "attribute_id" => $attribute->id,
                        "name" => Str::random(10)
                    ]);
                }
                $value[] = $attribute_option->id;
                break;

            case "image":
                $value = UploadedFile::fake()->image('image.jpeg');
                break;

            case "multiimage":
                $value[] = UploadedFile::fake()->image('image.jpeg');
                break;

            default:
                $value = Str::random(10);
                break;
        }
        return $value;
    }

    public function getScope($websiteId)
    {
        $scope = Arr::random([ "website", "channel", "store" ]);
        $channels = Website::find($websiteId)->channels;
        if(count($channels) > 0 ){
            switch($scope)
            {
                case "website":
                    $scope_id = $websiteId;
                    break;

                case "channel":
                    $scope_id = $channels->first()->id;
                    break;

                case "store":
                    $stores = $channels->first()->stores;
                    $scope_id = (count($stores) > 0) ? $stores->first()->id : $this->getScope("channel", $websiteId);
                    break;
            }
        }
        return [
            "scope" => isset($scope_id) ? $scope : "website",
            "scope_id" => isset($scope_id) ? $scope_id : $websiteId
        ];

    }


}
