<?php

namespace Modules\Tax\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Tax\Entities\TaxRate;

class TaxRateTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = TaxRate::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Tax Rate";
        $this->route_prefix = "admin.taxes.rates";
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "zip_code" => null
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "use_zip_range" => "Invalid data type"
        ]);
    }
}
