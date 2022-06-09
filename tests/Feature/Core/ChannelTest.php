<?php

// namespace Tests\Feature\Core;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Support\Str;
// use Modules\Core\Entities\Channel;
// use Modules\Core\Entities\Currency;
// use Modules\Core\Entities\Locale;
// use Tests\AuthTestCase;


// class ChannelTest extends AuthTestCase
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

//     public function test_admin_can_fetch_channels()
//     {
//         $channel = factory(Channel::class)->create();
//         $response = $this->get(route('admin.channels.index'));
//         $response->assertJsonFragment([
//             "code" => $channel->code,
//             "name" => $channel->name
//         ]);
//     }

//     public function test_admin_can_fetch_a_channel()
//     {
//         $channel = factory(Channel::class)->create();

//         $response = $this->get(route('admin.channels.show',$channel->id));

//         $response->assertJsonFragment([
//             "code" => $channel->code,
//             "name" => $channel->name
//         ]);
//     }

//     public function test_admin_can_store_a_channel()
//     {

//         $channel = factory(Channel::class)->make();
//         $request_input = $channel->toArray();
//         $locales = Locale::pluck('id')->toArray();
//         $currencies = Currency::pluck('id')->toArray();
//         $request_input  = array_merge($request_input,[
//             'locales' => $locales,
//             'currencies' => $currencies,
//             'theme' => 'default'
//         ]);
//         $response = $this->post(route('admin.channels.store'),$request_input);
//         $response->assertJsonFragment([
//             "code" => $channel->code,
//             "name" => $channel->name
//         ]);
//         $response->assertJsonFragment([
//             "message" => "Channel created successfully."
//         ]);
//     }

//     public function test_admin_can_update_a_channel()
//     {

//         $channel = factory(Channel::class)->create();
//         $new_name = Str::random(16);
//         $request_input  = [
//             'theme' => 'default',
//             "name" => $new_name
//         ];

//         $response = $this->put(route('admin.channels.update', $channel->id),$request_input);
//         $response->assertJsonFragment([
//             "name" => $new_name
//         ]);
//         $response->assertJsonFragment([
//             "message" => "Channel updated successfully."
//         ]);
//     }

//     public function test_admin_can_delete_a_channel(){

//         $channel = factory(Channel::class)->create();
//         $response = $this->delete(route('admin.channels.delete', $channel->id));
//         $response->assertJsonFragment([
//             "message" => "Channel deleted successfully."
//         ]);

//     }

// }
