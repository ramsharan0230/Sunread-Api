<?php

namespace Modules\User\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\User\Entities\Admin;

class UserInvitationTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Admin::class;

        parent::setUp();

        $this->model_name = "Admin";
        $this->route_prefix = "admin";
        $this->hasIndexTest = false;
        $this->hasShowTest = false;
        $this->hasStoreTest = false;
        $this->hasUpdateTest = false;
        $this->hasDestroyTest = false;
        $this->hasBulkDestroyTest = false;
        $this->hasStatusTest = false;
    }

    public function createAdmin(array $attributes = []): object
    {
        return $this->model::factory()->create(["invitation_token" => \Str::random(20) ]);
    }

    public function testUserShouldBeAbleToAcceptInvitation()
    {
        $post_data = [
            "invitation_token" => $this->createAdmin()->invitation_token,
            "password" => "new_password",
            "password_confirmation" => "new_password",
        ];
        $response = $this->post($this->getRoute("accept-invitation", $post_data));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.update-success", ["name" => $this->model_name])
        ]);
    }

    public function testUserShouldNotBeAbleToAcceptInvitationWithInvalidToken()
    {
        $post_data = [
            "invitation_token" => "Invalid_token",
            "password" => "new_password",
            "password_confirmation" => "new_password",
        ];
        $response = $this->post($this->getRoute("accept-invitation", $post_data));

        $response->assertForbidden();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.users.token.token-missing")
        ]);
    }

    public function testUserShouldBeAbleToGetInvitationToken()
    {
        $token = $this->createAdmin()->invitation_token;
        $response = $this->get($this->getRoute("invitation-info", [$token]));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success",  ["name" => $this->model_name])
        ]);
    }

    public function testShouldReturnErrorIfInvitationTokenInvalid()
    {
        $token = "Invalid_Token";
        $response = $this->get($this->getRoute("invitation-info", [$token]));

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => $this->model_name])
        ]);
    }
}
