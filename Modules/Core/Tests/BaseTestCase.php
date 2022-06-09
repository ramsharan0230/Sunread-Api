<?php

namespace Modules\Core\Tests;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Modules\User\Entities\Role;
use Modules\User\Entities\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Traits\ResponseMessage;
use Symfony\Component\HttpFoundation\Response;

class BaseTestCase extends TestCase
{
    use DatabaseTransactions;
    use ResponseMessage;

    protected array $headers;
    protected static $seederRun = false;

    public $model;
    public string $model_name;
    public string $route_prefix;
    public $filter;
    public int $default_resource_id;
    public int $fake_resource_id;
    public int $factory_count;
    public mixed $append_to_route;

    public bool $createFactories;
    public bool $hasFilters;
    public bool $hasIndexTest;
    public bool $hasAllTest;
    public bool $hasShowTest;
    public bool $hasStoreTest;
    public bool $hasUpdateTest;
    public bool $hasDestroyTest;
    public bool $hasBulkDestroyTest;
    public bool $hasStatusTest;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory_count = 2;
        $this->append_to_route = null;
        $this->default_resource_id = $this->model::latest('id')->first()->id;
        $this->fake_resource_id = 0;
        $this->filter = [
            "sort_by" => "id",
            "sort_order" => "asc"
        ];

        // Tests skipping
        $this->createFactories = true;
        $this->hasFilters = true;
        $this->hasIndexTest = true;
        $this->hasAllTest= false;
        $this->hasShowTest = true;
        $this->hasStoreTest = true;
        $this->hasUpdateTest = true;
        $this->hasDestroyTest = true;
        $this->hasBulkDestroyTest = false;
        $this->hasStatusTest = false;
    }

    /**
     * Factory methods
     */
    public function getCreateData(): array
    {
        return $this->model::factory()->make()->toArray();
    }

    public function getNonMandatoryCreateData(): array
    {
        return $this->getCreateData();
    }

    public function getInvalidCreateData(): array
    {
        return $this->getCreateData();
    }

    public function getUpdateData(): array
    {
        return $this->getCreateData();
    }

    public function getNonMandatoryUpdateData(): array
    {
        return $this->getNonMandatoryCreateData();
    }

    public function getInvalidUpdateData(): array
    {
        return $this->getInvalidCreateData();
    }

    /**
     * Generate Admin data
     */
    public function createAdmin(array $attributes = []): object
    {
        try
        {
            $password = $attributes["password"] ?? "password";
            $role_slug = $attributes["role_slug"] ?? "super-admin";
            $role = Role::where("slug", $role_slug)->firstOrFail();

            $data = [
                "password" => Hash::make($password),
                "role_id" => $role->id,
            ];

            $admin = Admin::factory()->create($data);
            $token = $this->createToken($admin->email, $password);
            $this->headers["Authorization"] = "Bearer {$token}";
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

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

    public function getRoute(string $method, ?array $parameters = null): string
    {
        $parameters = $this->append_to_route ? array_merge([$this->append_to_route], $parameters ?? []) : $parameters;
        return route("{$this->route_prefix}.{$method}", $parameters);
    }

    /**
     * GET tests
     *
     * 1. Assert if resource list can be fetched
     * 2. Assert if resource list can be fetched with filter
     * 3. Assert if individual resource can be fetched
     * 4. Assert if application returns correct error for invalid resource id
     */

    public function testAdminCanFetchResources()
    {
        if (!$this->hasIndexTest) {
            $this->markTestSkipped("Index method not available.");
        }
        if ($this->createFactories) {
            $this->model::factory($this->factory_count)->create();
        }

        $response = $this->withHeaders($this->headers)->get($this->getRoute("index"));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanFetchFilteredResources()
    {
        if (!$this->hasIndexTest) {
            $this->markTestSkipped("Index method not available.");
        }
        if (!$this->hasFilters) {
            $this->markTestSkipped("Filters not available.");
        }

        $response = $this->withHeaders($this->headers)->get($this->getRoute("index", $this->filter));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanFetchResourcesWithoutPagination()
    {
        if (!$this->hasAllTest) {
            $this->markTestSkipped("All method not available.");
        }
        $response = $this->withHeaders($this->headers)->get($this->getRoute("all"));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanFetchIndividualResource()
    {
        if (!$this->hasShowTest) {
            $this->markTestSkipped("Show method not available.");
        }
        $response = $this->withHeaders($this->headers)->get($this->getRoute("show", [$this->default_resource_id]));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }

    public function testShouldReturnErrorIfResourceDoesNotExist()
    {
        if (!$this->hasShowTest) {
            $this->markTestSkipped("Show method not available.");
        }
        $response = $this->withHeaders($this->headers)->get($this->getRoute("show", [$this->fake_resource_id]));
        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => $this->model_name])
        ]);
    }

    /**
     * POST tests
     *
     * 1. Assert if resource can be created
     * 2. Assert if resource can be created with non mandatory data
     * 3. Assert if application returns correct error for invalid data
     */

    public function testAdminCanCreateResource()
    {
        if (!$this->hasStoreTest) {
            $this->markTestSkipped("Store method not available.");
        }
        $post_data = $this->getCreateData();
        $response = $this->withHeaders($this->headers)->post($this->getRoute("store"), $post_data);

        $response->assertCreated();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.create-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanCreateResourceWithNonMandatoryData()
    {
        if (!$this->hasStoreTest) {
            $this->markTestSkipped("Store method not available.");
        }
        $post_data = $this->getNonMandatoryCreateData();
        $response = $this->withHeaders($this->headers)->post($this->getRoute("store"), $post_data);

        $response->assertCreated();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.create-success", ["name" => $this->model_name])
        ]);
    }

    public function testShouldReturnErrorIfCreateDataIsInvalid()
    {
        if (!$this->hasStoreTest) {
            $this->markTestSkipped("Store method not available.");
        }
        $post_data = $this->getInvalidCreateData();
        $response = $this->withHeaders($this->headers)->post($this->getRoute("store"), $post_data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    /**
     * PUT tests
     *
     * 1. Assert if resource can be updated
     * 2. Assert if resource can be updated with non mandatory data
     * 3. Assert if application returns correct error for invalid data
     * 4. Assert if application returns correct error when trying to update non-existent data
     */

    public function testAdminCanUpdateResource()
    {
        if (!$this->hasUpdateTest) {
            $this->markTestSkipped("Update method not available.");
        }
        $post_data = $this->getUpdateData();
        $response = $this->withHeaders($this->headers)->put($this->getRoute("update", [$this->default_resource_id]), $post_data);

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.update-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanUpdateResourceWithNonMandatoryData()
    {
        if (!$this->hasUpdateTest) {
            $this->markTestSkipped("Update method not available.");
        }
        $post_data = $this->getNonMandatoryUpdateData();
        $response = $this->withHeaders($this->headers)->put($this->getRoute("update", [$this->default_resource_id]), $post_data);

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.update-success", ["name" => $this->model_name])
        ]);
    }

    public function testShouldReturnErrorIfUpdateDataIsInvalid()
    {
        if (!$this->hasUpdateTest) {
            $this->markTestSkipped("Update method not available.");
        }
        $post_data = $this->getInvalidUpdateData();
        $response = $this->withHeaders($this->headers)->put($this->getRoute("update", [$this->default_resource_id]), $post_data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testShouldReturnErrorIfUpdateResourceDoesNotExist()
    {
        if (!$this->hasUpdateTest) {
            $this->markTestSkipped("Update method not available.");
        }
        $post_data = $this->getUpdateData();
        $response = $this->withHeaders($this->headers)->put($this->getRoute("update", [$this->fake_resource_id]), $post_data);

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => $this->model_name])
        ]);
    }

    /**
     * DELETE tests
     *
     * 1. Assert if resource can be deleted
     * 2. Assert if application returns correct error when trying to delete non-existent data
     */

    public function testAdminCanDeleteResource()
    {
        if (!$this->hasDestroyTest) {
            $this->markTestSkipped("Destroy method not available.");
        }
        $resource_id = $this->model::factory()->create()->id;
        $response = $this->withHeaders($this->headers)->delete($this->getRoute("destroy", [$resource_id]));

        $response->assertOk();

        $check_resource = $this->model::whereId($resource_id)->first() ? true : false;
        $this->assertFalse($check_resource);
    }

    public function testShouldReturnErrorIfDeleteResourceDoesNotExist()
    {
        if (!$this->hasDestroyTest) {
            $this->markTestSkipped("Destroy method not available.");
        }
        $response = $this->withHeaders($this->headers)->delete($this->getRoute("destroy", [$this->fake_resource_id]));

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => $this->model_name])
        ]);
    }

    /**
     * STATUS tests
     *
     * 1. Assert if reource's status can be updated
     * 2. Assert if application returns correct error when trying to update non-existent data
     */

    public function testAdminCanUpdateResourceStatus()
    {
        if (!$this->hasStatusTest) {
            $this->markTestSkipped("Status update method not available.");
        }
        $resource = $this->model::factory()->create(["status" => 1]);
        $response = $this->withHeaders($this->headers)->put($this->getRoute("status", [$resource->id]));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.status-updated", ["name" => $this->model_name])
        ]);

        // Checking if status has been updated
        $expected_resource_status = !$resource->status;
        $updated_resource_status = $this->model::find($resource->id)->status;
        $this->assertTrue($updated_resource_status == $expected_resource_status);
    }

    public function testAdminCanUpdateResourceStatusToSpecific()
    {
        if (!$this->hasStatusTest) {
            $this->markTestSkipped("Status update method not available.");
        }
        $resource = $this->model::factory()->create(["status" => 1]);
        $expected_resource_status = rand(0, 1);
        $response = $this->withHeaders($this->headers)->put($this->getRoute("status", [$resource->id]), [
            "status" => $expected_resource_status
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.status-updated", ["name" => $this->model_name])
        ]);

        // Checking if status has been updated
        $updated_resource_status = $this->model::find($resource->id)->status;
        $this->assertTrue($updated_resource_status == $expected_resource_status);
    }

    public function testShouldReturnErrorIfUpdateStatusResourceDoesNotExist()
    {
        if (!$this->hasStatusTest) {
            $this->markTestSkipped("Status update method not available.");
        }
        $response = $this->withHeaders($this->headers)->put($this->getRoute("status", [$this->fake_resource_id]));

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => $this->model_name])
        ]);
    }
}
