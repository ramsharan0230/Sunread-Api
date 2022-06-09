<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Entities\ExchangeRate;
use Modules\Core\Tests\BaseTestCase;

class ExchangeRateTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = ExchangeRate::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Exchange Rate";
        $this->route_prefix = "admin.exchange_rates";
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "rate" => null
        ]);
    }
}
