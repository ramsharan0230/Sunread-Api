<?php

namespace Modules\Tax\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Tax\Entities\CustomerTaxGroup;

class CustomerTaxGroupTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = CustomerTaxGroup::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Customer Tax Group";
        $this->route_prefix = "admin.taxes.groups.customers";
        $this->hasAllTest= true;
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "name" => null
        ]);
    }
}
