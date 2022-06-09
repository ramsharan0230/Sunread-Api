<?php

namespace Modules\NavigationMenu\Tests\Feature;

use Tests\TestCase;
use Modules\Core\Entities\Website;
use Modules\Core\Tests\BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\NavigationMenu\Entities\NavigationMenu;

class NavigationMenuTest extends BaseTestCase
{
    protected $default_resource;

    public function setUp(): void
    {
        $this->model = NavigationMenu::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Navigation Menu";
        $this->route_prefix = "admin.navigation-menus";
        $this->hasStatusTest = false;
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "testing" => null
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "title" => null,
            "slug" => null,
            "status" => null,
            "website_id" => null
        ]);
    }
}
