<?php

// namespace Tests\Feature\Core;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Support\Str;
// use Modules\Core\Entities\Currency;
// use Tests\AuthTestCase;


// class CurrencyTest extends AuthTestCase
// {
//     use RefreshDatabase;

//     protected $admin;

//     protected $headers;

//     public function setUp(): void
//     {
//         parent::setUp();
//         $this->admin = $this->createAdmin([
//             'password' => 'password',
//         ]);
//     }

//     public function test_admin_can_fetch_currencies()
//     {
//         $currency = factory(Currency::class)->create();
//         $response = $this->get(route('admin.currencies.index'));
//         $response->assertJsonFragment([
//             "code" => $currency->code,
//             "name" => $currency->name,
//         ]);
//     }

//     public function test_admin_can_fetch_a_currencies()
//     {
//         $currency = factory(Currency::class)->create();
//         $response = $this->get(route('admin.currencies.show',$currency->id));
//         $response->assertJsonFragment([
//             "code" => $currency->code,
//             "name" => $currency->name
//         ]);
//     }

//     public function test_admin_can_store_a_currencies()
//     {

//         $currency = factory(Currency::class)->make();
//         $request_input = $currency->toArray();
//         $response = $this->post(route('admin.currencies.store'),$request_input);
//         $response->assertJsonFragment([
//             "code" => $currency->code,
//             "name" => $currency->name
//         ]);
//         $response->assertJsonFragment([
//             "message" => "Currency created successfully."
//         ]);
//     }

//     public function test_admin_can_update_a_currency()
//     {

//         $currency = factory(Currency::class)->create();
//         $new_name = Str::random(16);
//         $request_input  = [
//             "name" => $new_name
//         ];
//         $response = $this->put(route('admin.currencies.update', $currency->id),$request_input);
//         $response->assertJsonFragment([
//             "name" => $new_name
//         ]);
//         $response->assertJsonFragment([
//             "message" => "Currency updated successfully."
//         ]);
//     }

//     public function test_admin_can_delete_a_currency(){
//         $currency = factory(Currency::class)->create();
//         $response = $this->delete(route('admin.currencies.delete', $currency->id));
//         $response->assertJsonFragment([
//             "message" => "Currency deleted successfully."
//         ]);

//     }

// }
