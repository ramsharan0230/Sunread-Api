<?php

namespace Modules\Cart\Tests\Feature;

use Modules\Cart\Entities\Cart;
use Modules\Cart\Entities\CartItem;
use Modules\Core\Tests\StoreFrontBaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class CartTest extends StoreFrontBaseTestCase
{
    public array $headers;
    public ?object $customer;

    public function setUp(): void
    {
        $this->model = Cart::class;
        $this->cart_item = CartItem::class;

        parent::setUp();
        $this->createHeader();
        $this->authentication = false;
        $this->customer = $this->createCustomer();
        $this->model_name = "Cart";
        $this->route_prefix = "cart";

        $this->createFactories = true;
        $this->hasFilters = false;
        $this->hasIndexTest = false;
        $this->hasShowTest = false;
    }

    public function getCreateData(): array
    {
        $data = [
            "product_id" => 1,
            "qty" => 2,
        ];
        return $data;
    }

    public function getCartCreateData(int $customer_id = null): object
    {
        $cart = $this->model::factory()->create([
                "customer_id" => $customer_id,
                "channel_code" => $this->headers["hc-channel"],
                "store_code" => $this->headers["hc-store"]
            ]);
        $this->cart_item::factory()->create([
            "cart_id" => $cart->id
        ]);
        return $cart;
    }

    public function testCheckProductExistOnChannel(): void
    {
        $data = $this->getCreateData();
        $post_data = array_merge($data, [
            "type" => "create"
        ]);

        $response = $this->withHeaders($this->headers)->post($this->getRoute("add.update"), $post_data);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", [ "name" => "Product" ])
        ]);
    }

    public function testCheckProductInStock(): void
    {
        $this->headers = $this->getDefaultHeaders();

        $post_data = array_merge($this->getCreateData(), [
            "qty" => 1000,
            "type" => "create",
        ]);
        $response = $this->withHeaders($this->headers)->post($this->getRoute("add.update"), $post_data);
        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-enough-stock-quantity")
        ]);
    }

    public function testGuestCanCreateCart(): void
    {
        $this->createCart();
    }

    public function testCustomerCanCreateCart(): void
    {
        $this->authentication = true;
        $this->createCustomer();
        $this->createCart();
    }

    public function testGuestCanUpdateCart(): void
    {
        $cart = $this->getCartCreateData();
        $this->headers = $this->getDefaultHeaders();
        $headers = array_merge($this->headers,[
            "hc-cart" => $cart->id
        ]);

        $data = $this->getCreateData();
        $post_data = array_merge($data, [
            "type" => "update"
        ]);

        $response = $this->withHeaders($headers)->post($this->getRoute("add.update"), $post_data);
        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __($response["message"])
        ]);
    }

    public function testCustomerCanUpdateCart(): void
    {
        $this->authentication = true;
        $this->customer = $this->createCustomer();

        $cart = $this->getCartCreateData($this->customer->id);
        $this->headers = $this->getDefaultHeaders();

        $headers = array_merge($this->headers,[
            "hc-cart" => $cart->id
        ]);
        $data = $this->getCreateData();
        $post_data = array_merge($data, [
            "type" => "update"
        ]);

        $response = $this->withHeaders($headers)->post($this->getRoute("add.update"), $post_data);
        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __($response["message"])
        ]);
    }

    public function testShouldCreateNewCartIfCartNotExist(): void
    {
        $this->headers = $this->getDefaultHeaders();

        $headers = array_merge($this->headers,[
            "hc-cart" => "random-string"
        ]);
        $data = $this->getCreateData();
        $post_data = array_merge($data, [
            "type" => "update"
        ]);

        $response = $this->withHeaders($headers)->post($this->getRoute("add.update"), $post_data);
        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __($response["message"])
        ]);
    }

    public function testGuestCanDeleteProductFromCart(): void
    {
        $this->headers = $this->getDefaultHeaders();

        $cart = $this->getCartCreateData();
        $headers = array_merge($this->headers,[
            "hc-cart" => $cart->id
        ]);

        $product_id = $cart->cartItems->first()->product_id;
        $post_data = [ "product_id" => $product_id ];

        $response = $this->withHeaders($headers)->delete($this->getRoute("delete.product.from.cart"), $post_data);
        $response->assertOk();
        $check_resource = $this->cart_item::whereCartId($cart->id)->whereProductId($product_id)->first() ? true : false;
        $this->assertFalse($check_resource);
    }

    public function testShouldReturnErrorIfDeleteResourceDoesNotExist(): void
    {
        $post_data = [ "product_id" => 1 ];
        $response = $this->withHeaders($this->headers)->delete($this->getRoute("delete.product.from.cart"), $post_data);

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", [ "name" => $this->model_name ])
        ]);
    }

    public function testCustomerCanDeleteProductFromCart(): void
    {
        $this->authentication = true;
        $this->customer = $this->createCustomer();
        $cart = $this->getCartCreateData($this->customer->id);

        $headers = array_merge($this->headers,[
            "hc-cart" => $cart->id
        ]);

        $product_id = $cart->cartItems->first()->product_id;
        $post_data = [
            "customer_id" => $this->customer->id,
            "product_id" => $product_id
        ];

        $response = $this->withHeaders($headers)->delete($this->getRoute("delete.product.from.cart"), $post_data);
        $response->assertOk();
        $check_resource = $this->cart_item::whereCartId($cart->id)->whereProductId($product_id)->first() ? true : false;
        $this->assertFalse($check_resource);
    }

    // public function testGuestCanFetchAllProductFromCart(): void
    // {
    //     $cart = $this->getCartCreateData();

    //     $headers = array_merge($this->headers, [
    //         "hc-cart" => $cart->id,
    //     ]);

    //     $response = $this->withHeaders($headers)->get($this->getRoute("products.from.cart"));
    //     $response->assertOk();
    //     $response->assertJsonFragment([
    //         "status" => "success",
    //         "message" => __("core::app.response.fetch-success", [ "name" => $this->model_name ])
    //     ]);
    // }

    public function testGuestShouldReturnErrorIfRandomCart(): void
    {
        $headers = array_merge($this->headers, [
            "hc-cart" => "random-cart",
        ]);

        $response = $this->withHeaders($headers)->get($this->getRoute("products.from.cart"));

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", [ "name" => $this->model_name ])
        ]);
    }

    // public function testCustomerCanFetchAllProductFromCart(): void
    // {
    //     $this->authentication = true;
    //     $this->customer = $this->createCustomer();
    //     $this->getCartCreateData($this->customer->id);
    //     $response = $this->withHeaders($this->headers)->get($this->getRoute("products.from.cart"));
    //     $response->assertOk();
    //     $response->assertJsonFragment([
    //         "status" => "success",
    //         "message" => __("core::app.response.fetch-success", [ "name" => $this->model_name ])
    //     ]);
    // }

    public function testGuestCanMergeCart(): void
    {
        $this->authentication = true;
        $this->customer = $this->createCustomer();
        $cart = $this->getCartCreateData();
        $headers = array_merge($this->headers, [
            "hc-cart" => $cart->id,
        ]);

        $response = $this->withHeaders($headers)->post($this->getRoute("merge.cart"));

        $response->assertJsonFragment([
            "status" => "success",
            "message" => __($response["message"])
        ]);
    }

    public function testShouldReturnErrorIfCustomerIdExist(): void
    {
        $this->authentication = true;
        $this->customer = $this->createCustomer();
        $cart = $this->getCartCreateData($this->customer->id);

        $headers = array_merge($this->headers, [
            "hc-cart" => $cart->id,
        ]);

        $response = $this->withHeaders($headers)->post($this->getRoute("merge.cart"));

        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.exception_message.not-allowed")
        ]);
    }

    private function getDefaultHeaders(): array
    {
        $headers = array_merge($this->headers, [
            "hc-host" => "international.co",
            "hc-channel" => "international",
            "hc-store" => "international-store",
        ]);

        return $headers;
    }

    public function createCart(): void
    {
        $this->headers = $this->getDefaultHeaders();

        $data = $this->getCreateData();
        $post_data = array_merge($data, [
            "type" => "create"
        ]);

        $response = $this->withHeaders($this->headers)->post($this->getRoute("add.update"), $post_data);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __($response["message"])
        ]);
    }
}
