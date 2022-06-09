<?php

namespace Modules\Product\Tests\Feature;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Modules\Core\Tests\BaseTestCase;
use Modules\Product\Entities\Product;
use Illuminate\Support\Facades\Storage;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Product\Entities\ProductImage;
use Modules\Core\Entities\Website;
use Modules\Product\Entities\Feature;
use Modules\Tax\Entities\CustomerTaxGroup;

class ProductTest extends BaseTestCase
{
    public $default_resource, $filter;
    public function setUp(): void
    {
        $this->model = Product::class;
        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->filter = [
            "website_id" => 1
        ];
        $this->model_name = "Product";
        $this->route_prefix = "admin.catalog.products";
        $this->default_resource = $this->model::latest('id')->first();
        $this->default_resource_id = $this->default_resource->id;
        $this->hasStatusTest = true;
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

        return array_merge($merge_product, ["attributes" => $attributes]);
    }

    public function getUpdateData(): array
    {
        $websiteId = $this->default_resource->website_id;
        $updateData = $this->getCreateData();
        return array_merge($updateData, $this->getScope($websiteId));
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

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
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

    public function getInvalidUpdateData(): array
    {
        return array_merge($this->getUpdateData(), [
            "parent_id" => 0
        ]);
    }

    public function testAdminCanFetchResources()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("index", $this->filter));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    /**
     * Product Image Tests
     */

    public function testAdminCanUploadProductImage()
    {
        Storage::fake();

        $post_data = [
            "product_id" => $this->default_resource_id,
            "image" => [
                UploadedFile::fake()->image('image.jpeg'),
                UploadedFile::fake()->image('image-2.jpeg')
            ]
        ];

        $response = $this->withHeaders($this->headers)->post($this->getRoute("image.store"), $post_data);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.create-success", ["name" => "Product Image"])
        ]);
    }

    public function testAdminShouldBeAbleToChangeMainProductImage()
    {
        $product_image = ProductImage::factory(3)->create()->first();
        $product_image_id = $product_image->id;
        $expected_main_image = !$product_image->main_image;

        $response = $this->withHeaders($this->headers)->put($this->getRoute("image.change_main_image", [$product_image_id]));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.status-change-success", ["name" => "Product Image"])
        ]);

        // Check if main_image has been changed as expected
        $product_image = ProductImage::whereId($product_image_id)->first();
        $this->assertEquals($product_image->main_image, $expected_main_image);

        // CHeck if other images of the products has been changed to 0
        $other_images = ProductImage::whereProductId($product_image->product_id)
            ->whereMainImage(1)
            ->where("id", "<>", $product_image->id)
            ->get();
        $this->assertEquals($other_images->count(), 0);
    }

    public function testAdminShouldBeAbleToDeleteProductImage()
    {
        $product_image_id = ProductImage::factory()->create()->id;
        $response = $this->withHeaders($this->headers)->delete($this->getRoute("image.destroy", [$product_image_id]));

        $response->assertOk();
    }

    public function testShouldReturnErrorIfDeleteProductImageDoesNotExist()
    {
        $response = $this->withHeaders($this->headers)->delete($this->getRoute("image.destroy", [$this->fake_resource_id]));

        $response->assertStatus(404);
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => "Product Image"])
        ]);
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
