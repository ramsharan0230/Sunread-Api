<?php

namespace Modules\Country\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Country\Entities\Country;

class CountryTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Country::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Country";
        $this->route_prefix = "admin.country";

        $this->createFactories = false;
        $this->hasStoreTest = false;
        $this->hasUpdateTest = false;
        $this->hasDestroyTest = false;
    }
}
