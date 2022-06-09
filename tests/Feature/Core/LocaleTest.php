<?php

// namespace Tests\Feature\Core;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Support\Str;
// use Modules\Core\Entities\Locale;
// use Tests\AuthTestCase;


// class LocaleTest extends AuthTestCase
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

//     public function test_admin_can_fetch_locales()
//     {
//         $locale = factory(Locale::class)->create();
//         $response = $this->get(route('admin.locales.index'));
//         $response->assertJsonFragment([
//             "code" => $locale->code,
//             "name" => $locale->name
//         ]);
//     }

//     public function test_admin_can_fetch_a_locale()
//     {
//         $locale = factory(Locale::class)->create();
//         $response = $this->get(route('admin.locales.show',$locale->id));
//         $response->assertJsonFragment([
//             "code" => $locale->code,
//             "name" => $locale->name
//         ]);
//     }

//     public function test_admin_can_store_a_locale()
//     {

//         $locale = factory(Locale::class)->make();
//         $request_input = $locale->toArray();
//         $response = $this->post(route('admin.locales.store'),$request_input);
//         $response->assertJsonFragment([
//             "code" => $locale->code,
//             "name" => $locale->name
//         ]);
//         $response->assertJsonFragment([
//             "message" => "Locale created successfully."
//         ]);
//     }

//     public function test_admin_can_update_a_locale()
//     {

//         $locale = factory(Locale::class)->create();
//         $new_name = Str::random(16);
//         $request_input  = [
//             "name" => $new_name
//         ];
//         $response = $this->put(route('admin.locales.update', $locale->id),$request_input);
//         $response->assertJsonFragment([
//             "name" => $new_name
//         ]);
//         $response->assertJsonFragment([
//             "message" => "Locale updated successfully."
//         ]);
//     }

//     public function test_admin_can_delete_a_locale(){

//         $locale = factory(Locale::class)->create();
//         $response = $this->delete(route('admin.locales.delete', $locale->id));
//         $response->assertJsonFragment([
//             "message" => "Locale deleted successfully."
//         ]);

//     }

// }
