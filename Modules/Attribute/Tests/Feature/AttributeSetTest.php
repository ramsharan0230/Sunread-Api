<?php

namespace Modules\Attribute\Tests\Feature;

use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeSet;
use Modules\Core\Tests\BaseTestCase;
use Illuminate\Support\Str;

class AttributeSetTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = AttributeSet::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Attribute Set";
        $this->route_prefix = "admin.attribute.sets";
    }

    public function getCreateData(): array
    {
        return $this->model::factory()->make([
            "attribute_set_id" => AttributeSet::latest('id')->first()->id
        ])->toArray();
    }

    public function getUpdateData(): array
    {
        return $this->model::factory()->make([
            "groups" => [
                [
                    "name" => Str::random(10),
                    "position" => 1,
                    "attributes" => Attribute::whereIsUserDefined(0)->pluck('id')->toArray()
                ]
            ]
        ])->toArray();
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "name" => null
        ]);
    }
}
