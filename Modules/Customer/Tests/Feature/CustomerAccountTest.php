<?php

namespace Modules\Customer\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Customer\Entities\Customer;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use DatabaseTransactions;
    
    protected array $headers;

    public $route_prefix, $model_name, $customer, $default_resource_id;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = Customer::class;
        $this->customer = $this->createCustomer();
        $this->model_name = "Customer";
        $this->route_prefix = "customers.account";
    }

    public function getCreateData(): array {
        return $this->model::factory()->make([
            "password" => "password",
            "gender" => "male",
            "password_confirmation" => "password",
            "is_email_verified" => 1,
        ])->toArray(); 
    }
    
    public function getUpdateData(): array { return $this->getCreateData(); }

    public function createCustomer(array $attributes = []): object
    {
        $password = $attributes["password"] ?? "password";

        $data = [
            "password" => Hash::make($password),
            "is_email_verified" => 1,
        ];
        
        $customer = Customer::factory()->create($data);
        $token = $this->createToken($customer->email, $password);
        $this->headers["Authorization"] = "Bearer {$token}";

        return $customer;
    }

    public function createToken(string $customer_email, string $password): ?string
    {
        $jwtToken = Auth::guard("customer")
            ->setTTL( config("jwt.customer_jwt_ttl") )
            ->attempt([
                "email" => $customer_email,
                "password" => $password
            ]);
        return $jwtToken ?? null;
    }

    public function testCustomerCanFetchOwnProfile()
    {
        $response = $this->withHeaders($this->headers)->get(route("{$this->route_prefix}.show"));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }

    public function testCustomerCanUpdateProfile()
    {
        $post_data = $this->getUpdateData();
        $response = $this->withHeaders($this->headers)->post(route("{$this->route_prefix}.update", $post_data));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.update-success", ["name" => $this->model_name])
        ]);
    }

    public function testCustomerCanUpdateProfileImage()
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

    public function testCustomerShouldNotBeAbleToUpdateProfileImageWithInvalidImage()
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

    public function testCustomerShouldBeAbleToDeleteProfileImage()
    {
        $response = $this->withHeaders($this->headers)->delete(route("{$this->route_prefix}.image.delete"));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => "Profile image deleted successfully."
        ]);
    }


}
