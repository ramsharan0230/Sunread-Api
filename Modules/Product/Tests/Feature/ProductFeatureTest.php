<?php

namespace Modules\Product\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Core\Entities\Store;
use Modules\Core\Tests\BaseTestCase;
use Modules\Product\Entities\Feature;

class ProductFeatureTest extends BaseTestCase
{
    public $default_resource;

    public function setUp(): void
    {
        $this->model = Feature::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Feature";
        $this->route_prefix = "admin.catalog.features";
        $this->default_resource = $this->model::latest('id')->first();
        $this->default_resource_id = $this->default_resource->id;
        $this->hasStatusTest = true;
    }

    public function getCreateData(): array
    {
        Storage::fake();
        $translations = $this->getTranslations();
        return array_merge($this->model::factory()->make([
                "image" => UploadedFile::fake()->image("image.png")
            ])->toArray(), $translations);
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "description" => null
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "name" => null
        ]);
    }

    public function getTranslations(): array
    {
        return [
            "translations" => [
                [
                    "store_id" => Store::factory()->create()->id,
                    "name" => Str::random(10),
                    "description" => Str::random(20)
                ]
            ]
        ];
    }
}
