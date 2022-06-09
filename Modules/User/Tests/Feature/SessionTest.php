<?php

namespace Modules\User\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Modules\User\Entities\Role;
use Modules\User\Entities\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class SessionTest extends TestCase
{
	use DatabaseTransactions;

    protected object $admin, $fake_admin;
	protected array $headers;

	public function setUp(): void
	{
		parent::setUp();

        $this->admin = $this->createAdmin();
        $this->fake_admin = Admin::factory()->make();
	}

    public function createAdmin(array $attributes = []): object
	{
		$password = $attributes["password"] ?? "password";
		$role_slug = $attributes["role_slug"] ?? "super-admin";
		$role = Role::where("slug", $role_slug)->firstOrFail();

		$data = [
			"password" => Hash::make($password),
			"role_id" => $role->id
		];

        return Admin::factory()->create($data);
	}

    /**
     * Tests
     */

    public function testAdminCanLogin()
    {
        $post_data = [
            "email" => $this->admin->email,
            "password" => "password"
        ];
        $response = $this->post(route("admin.session.login"), $post_data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.users.users.login-success")
        ]);
    }

    public function testInvalidCredentialsShouldNotBeAbleToLogin()
    {
        $post_data = [
            "email" => $this->admin->email,
            "password" => "wrong_password"
        ];
        $response = $this->post(route("admin.session.login"), $post_data);

        $response->assertStatus(401);
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.users.users.login-error")
        ]);
    }

    public function testInvalidUserShouldNotBeAbleToLogin()
    {
        $post_data = [
            "email" => $this->fake_admin->email,
            "password" => null
        ];
        $response = $this->post(route("admin.session.login"), $post_data);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testAdminCanRequestResetLink()
    {
        $post_data = ["email" => $this->admin->email];
        $response = $this->post(route("admin.forget-password.store"), $post_data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => "Reset Link sent to your email {$this->admin->email}"
        ]);
    }

    public function testAdminCanResetPassword()
    {
        $reset_token = Password::broker()->createToken($this->admin);
        $post_data = [
            "email" => $this->admin->email,
            "password" => "new_password",
            "password_confirmation" => "new_password",
            "token" => $reset_token
        ];
        $response = $this->post(route("admin.reset-password.store"), $post_data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.users.users.password-reset-success")
        ]);
    }

    public function testInvalidAdminShouldNotBeAbleToRequestResetLink()
    {
        $post_data = ["email" => $this->fake_admin->email];
        $response = $this->post(route("admin.forget-password.store"), $post_data);

        $response->assertStatus(404);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testAdminShouldNotBeAbleToResetPasswordWithInvalidToken()
    {
        $reset_token = \Str::random(16);
        $post_data = [
            "email" => $this->admin->email,
            "password" => "new_password",
            "password_confirmation" => "new_password",
            "token" => $reset_token
        ];
        $response = $this->post(route("admin.reset-password.store"), $post_data);

        $response->assertStatus(401);
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.users.token.token-generation-problem")
        ]);
    }

    public function testAdminCanLogout()
    {
        $post_data = [
            "email" => $this->admin->email,
            "password" => "password"
        ];
        $response = $this->post(route("admin.session.login"), $post_data);
        $jwt_token = $response->json()["payload"]["data"]["token"];
        $this->headers["Authorization"] = "Bearer {$jwt_token}";

        /**
         * This logout should be successful because token is valid
         */
        $response = $this->withHeaders($this->headers)->get(route("admin.session.logout"));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.users.users.logout-success")
        ]);

        /**
         * This logout should be unsuccessful because token is invalidated
         */
        $response = $this->withHeaders($this->headers)->get(route("admin.session.logout"));
        $response->assertStatus(401);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }
}
