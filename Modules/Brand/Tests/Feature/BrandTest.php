<?php

namespace Modules\Brand\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Brand\Entities\Brand;
use Modules\Core\Tests\BaseTestCase;

class BrandTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Brand::class;

        parent::setUp();

        $this->admin = $this->createAdmin();
        $this->model_name = "Brand";
        $this->route_prefix = "admin.brands";
    }

    public function getCreateData(): array
    {
        Storage::fake();

        return $this->model::factory()->make([
            "image" => UploadedFile::fake()->image("image.png")
        ])->toArray();
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "name" => null
        ]);
    }

    public function getNonMandatoryUpdateData(): array
    {
        return array_merge($this->getUpdateData(), [
            "image" => null
        ]);
    }
}
