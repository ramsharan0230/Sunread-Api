<?php

namespace Modules\User\Tests\Feature;

use Modules\User\Entities\Role;
use Modules\Core\Tests\BaseTestCase;

class RoleTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Role::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Role";
        $this->route_prefix = "admin.roles";
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "description" => null
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "name" => null
        ]);
    }
}
