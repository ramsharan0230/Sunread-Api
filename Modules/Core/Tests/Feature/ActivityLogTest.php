<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Entities\ActivityLog;
use Modules\User\Entities\Admin;
use Modules\User\Entities\Role;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use DatabaseTransactions;

    protected object $admin;
    protected array $headers;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdmin();

        $this->model = ActivityLog::class;
        $this->model_name = "Activity Log";
        $this->route_prefix = "admin.activities";
        $this->fake_resource_id = 0;

        $this->filter = [
            "sort_by" => "id",
            "sort_order" => "asc"
        ];
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

    public function testTestingAllActivityLogsBecauseObserversIsntRunning()
    {
        // Create Resources
        $resource_id = $this->model::factory()->create()->id;
        $resource_ids = $this->model::factory(2)->create()->pluck("id")->toArray();

        // Admin can fetch resources
        $response = $this->withHeaders($this->headers)->get(route("{$this->route_prefix}.index"));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);

        // Admin can fetch filtered resources
        $response = $this->withHeaders($this->headers)->get(route("{$this->route_prefix}.index", $this->filter));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);

        // Admin can fetch individual resource
        $response = $this->get(route("{$this->route_prefix}.show", $resource_id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);

        // Should return 404 error if non existent data is requested
        $response = $this->withHeaders($this->headers)->get(route("{$this->route_prefix}.show", $this->fake_resource_id));

        $response->assertStatus(404);
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => $this->model_name])
        ]);


        // Admin Can Delete Individual resource
        $response = $this->withHeaders($this->headers)->delete(route("{$this->route_prefix}.destroy", $resource_id));

        $response->assertOk();

        $check_resource = $this->model::whereId($resource_id)->first() ? true : false;
        $this->assertFalse($check_resource);


        // Admin Can Delete Bulk resource0
        $response = $this->withHeaders($this->headers)->delete(route("{$this->route_prefix}.bulk-delete"), [
            "ids" => $resource_ids
        ]);

        $response->assertOk();

        $check_resource = $this->model::whereIn("id", $resource_ids)->get()->count() > 0 ? true : false;
        $this->assertFalse($check_resource);
    }
}
