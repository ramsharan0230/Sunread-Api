<?php

namespace Modules\Customer\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Modules\Core\Entities\Configuration;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomerGroup;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use DatabaseTransactions;

    protected object $customer, $fake_customer;
    protected array $headers;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->createCustomer();
        $this->fake_customer = Customer::factory()->make();
    }

    public function createCustomer(array $attributes = []): object
    {
        $password = $attributes["password"] ?? "password";
        $token =  Str::random(30);

        $data = [
            "password" => Hash::make($password),
            "customer_group_id" => CustomerGroup::first()->id,
            "verification_token" => $token,
        ];

        return Customer::factory()->create($data);
    }

    /**
     * Tests
    */

    public function testCustomerCanLogin(): void
    {
        $post_data = [
            "email" => $this->customer->email,
            "password" => "password",
        ];
        $response = $this->post(route("customers.session.login"), $post_data);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.users.users.login-success")
        ]);
    }

    public function testInvalidCredentialsShouldNotBeAbleToLogin(): void
    {
        $post_data = [
            "email" => $this->customer->email,
            "password" => "wrong_password"
        ];
        $response = $this->post(route("customers.session.login"), $post_data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.users.users.login-error")
        ]);
    }

    public function testInvalidUserShouldNotBeAbleToLogin(): void
    {
        $post_data = [
            "email" => $this->fake_customer->email,
            "password" => null,
        ];
        $response = $this->post(route("customers.session.login"), $post_data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testCustomerCanRequestResetLink(): void
    {
        $post_data = ["email" => $this->customer->email];
        $response = $this->post(route("customers.forget-password.store"), $post_data);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => "Reset Link sent to your email {$this->customer->email}"
        ]);
    }

    public function testCustomerCanResetPassword(): void
    {
        $reset_token = Password::broker('customers')->createToken($this->customer);
        $post_data = [
            "email" => $this->customer->email,
            "password" => "new_password",
            "password_confirmation" => "new_password",
            "token" => $reset_token,
        ];
        $response = $this->post(route("customers.reset-password.store"), $post_data);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.users.reset-password.password-reset-success")
        ]);
    }

    public function testInvalidCustomerShouldNotBeAbleToRequestResetLink(): void
    {
        $post_data = [ "email" => $this->fake_customer->email ];
        $response = $this->post(route("customers.forget-password.store"), $post_data);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testCustomerShouldNotBeAbleToResetPasswordWithInvalidToken(): void
    {
        $reset_token = \Str::random(16);
        $post_data = [
            "email" => $this->customer->email,
            "password" => "new_password",
            "password_confirmation" => "new_password",
            "token" => $reset_token,
        ];
        $response = $this->post(route("customers.reset-password.store"), $post_data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.users.token.token-generation-problem")
        ]);
    }

    public function testCustomerCanLogout(): void
    {
        $post_data = [
            "email" => $this->customer->email,
            "password" => "password",
        ];
        $response = $this->post(route("customers.session.login"), $post_data);
        $jwt_token = $response->json()["payload"]["data"]["token"];
        $this->headers["Authorization"] = "Bearer {$jwt_token}";

        /**
         * This logout should be successful because token is valid
         */
        $response = $this->withHeaders($this->headers)->get(route("customers.session.logout"));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.users.users.logout-success")
        ]);

        /**
         * This logout should be unsuccessful because token is invalidated
         */
        $response = $this->withHeaders($this->headers)->get(route("customers.session.logout"));
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testCustomerSendAccountConfirmationLink(): void
    {
        $post_data = [
            "email" => $this->customer->email,
            "password" => "password",
        ];
        $response = $this->post(route("customers.session.login"), $post_data);
        $jwt_token = $response->json()["payload"]["data"]["token"];
        $this->headers["Authorization"] = "Bearer {$jwt_token}";

        /**
         * create configuration factory to retrieve email template
         */
        Configuration::factory()->make()->create([
            "scope" => "website",
            "path" => "require_email_confirmation",
            "scope_id" => $this->customer->website_id,
            "value" => 1,
        ]);
        $response = $this->withHeaders($this->headers)->post(route("customers.account-confirmation.store"));

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.send-confirmation-link")
        ]);
    }

    public function testCustomerVerifyAccount(): void
    {
        $store = $this->customer->store;
        $store_code = $store->code;
        $channel_code = $store->channel->code;
        $response = $this->get(route("customers.account-verify", [
            $channel_code,
            $store_code,
            $this->customer->verification_token,
        ]));

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.verification-success")
        ]);
    }
}
