<?php

namespace Modules\User\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Modules\User\Entities\Role;
use Modules\User\Entities\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AccountTest extends TestCase
{
    use DatabaseTransactions;

    protected object $admin, $fake_admin;
    protected array $headers;
    public $model, $model_name, $route_prefix;

    public function setUp(): void
    {
        $this->model = Admin::class;

        parent::setUp();

        $this->admin = $this->createAdmin();
        $this->fake_admin = $this->model::factory()->make();

        $this->model_name = "Admin account";
        $this->route_prefix = "admin.account";
    }

    /**
     * Generate Admin data
     */
    public function createAdmin(array $attributes = []): object
    {
        $password = $attributes["password"] ?? "password";
        $role_slug = $attributes["role_slug"] ?? "super-admin";
        $role = Role::where("slug", $role_slug)->firstOrFail();

        $data = [
            "password" => Hash::make($password),
            "role_id" => $role->id
        ];
        
        $admin = Admin::factory()->create($data);
        $token = $this->createToken($admin->email, $password);
        $this->headers["Authorization"] = "Bearer {$token}";

        return $admin;
    }

    public function createToken(string $admin_email, string $password): ?string
    {
        $jwtToken = Auth::guard("admin")
            ->setTTL( config("jwt.admin_jwt_ttl") )
            ->attempt([
                "email" => $admin_email,
                "password" => $password
            ]);
        return $jwtToken ?? null;
    }

    /**
     * Tests
     */

    public function testAdminCanFetchAccountDetails()
    {
        $response = $this->withHeaders($this->headers)->get(route("{$this->route_prefix}.show"));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminShouldNotBeAbleToFetchAccountDetailsWithoutAuth()
    {
        $response = $this->withHeaders(["Authorization" => "Bearer invalid_token"])->get(route("{$this->route_prefix}.show"));

        $response->assertStatus(401);
        $response->assertJsonFragment([
            "status" => "error",
            "message" => "Unauthenticated."
        ]);
    }

    public function testAdminCanUpdateAccountDetails()
    {
        $post_data = array_merge($this->model::factory()->make()->toArray());

        $response = $this->withHeaders($this->headers)->put(route("{$this->route_prefix}.update"), $post_data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.update-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanUpdateAccountPassword()
    {
        $post_data = [
            "current_password" => "password",
            "password" => "new_password",
            "password_confirmation" => "new_password"
        ];

        $response = $this->withHeaders($this->headers)->put(route("{$this->route_prefix}.password"), $post_data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.update-success", ["name" => "Password"])
        ]);
    }

    public function testAdminShouldNotBeAbleToUpdateAccountDetailsWithInvalidPassword()
    {
        $post_data = [
            "current_password" => "invalid_password",
            "password" => "new_password",
            "password_confirmation" => "new_password"
        ];

        $response = $this->withHeaders($this->headers)->put(route("{$this->route_prefix}.password"), $post_data);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testAdminCanUpdateProfileImage()
    {
        Storage::fake();
        $post_data = [
            "image" => UploadedFile::fake()->image("image.png")
        ];

        $response = $this->withHeaders($this->headers)->post(route("{$this->route_prefix}.image.update"), $post_data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => "Profile image updated successfully."
        ]);
    }

    public function testAdminShouldNotBeAbleToUpdateProfileImageWithInvalidImage()
    {
        $post_data = [
            "image" => null
        ];

        $response = $this->withHeaders($this->headers)->post(route("{$this->route_prefix}.image.update"), $post_data);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testAdminShouldBeAbleToDeleteProfileImage()
    {
        $response = $this->withHeaders($this->headers)->delete(route("{$this->route_prefix}.image.delete"));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => "Profile image deleted successfully."
        ]);
    }
}
