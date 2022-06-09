<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Entities\Currency;
use Modules\Core\Tests\BaseTestCase;

class CurrencyTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Currency::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Currency";
        $this->route_prefix = "admin.currencies";
        $this->factory_count = 2;
        $this->hasStatusTest = true;
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "code" => null
        ]);
    }
}
