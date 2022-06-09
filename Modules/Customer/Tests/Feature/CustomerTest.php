<?php

namespace Modules\Customer\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Customer\Entities\Customer;

class CustomerTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Customer::class;

        parent::setUp();

        $this->admin = $this->createAdmin();
        $this->model_name = "Customer";
        $this->route_prefix = "admin.customers";
        $this->hasStatusTest = true;
    }

    public function getCreateData(): array
    {
        return array_merge($this->model::factory()->make()->toArray(), [
            "password" => "password",
            "gender" => "male",
            "password_confirmation" => "password"
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "email" => null
        ]);
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "customer_group_id" => null
        ]);
    }
}
