<?php

namespace Modules\Tax\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Tax\Entities\TaxRule;

class TaxRuleTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = TaxRule::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Tax Rule";
        $this->route_prefix = "admin.taxes.rules";
        $this->hasStatusTest = false;
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "subtotal" => null
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "customer_group_class" => null,
            "product_taxable_class" => null,
            "name" => null
        ]);
    }
}
