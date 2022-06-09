<?php

namespace Modules\Tax\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Tax\Entities\ProductTaxGroup;

class ProductTaxGroupTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = ProductTaxGroup::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Product Tax Group";
        $this->route_prefix = "admin.taxes.groups.products";
        $this->hasAllTest= true;
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "name" => null
        ]);
    }
}
