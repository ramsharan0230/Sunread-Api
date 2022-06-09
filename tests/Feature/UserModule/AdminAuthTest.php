<?php

// namespace Tests\Feature\UserModule;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\WithFaker;
// use Modules\User\Entities\Admin;
// use Tests\AuthTestCase;
// use Tests\TestCase;

// class AdminAuthTest extends AuthTestCase
// {
//     /**
//      * A basic feature test example.
//      *
//      * @return void
//      */
//     use RefreshDatabase;

//     public function test_admin_login()
//     {
//         $admin  = $this->createAdmin([
//             'password' => 'password',
//         ]);
//         $data = [
//             'email' => $admin->email,
//             'password' => 'password'
//         ];
//         $response = $this->post('/api/admin/login' ,$data);
//         $response->assertStatus(200);
//         $response->assertJsonFragment([
//             "status" => "success",
//             "message" => "Logged in successfully."
//         ]);
//     }

//     public function test_admin_logout()
//     {

//         $this->createAdmin();
//         $response = $this->get('/api/admin/logout' , $this->headers);
//         $response->assertStatus(200);
//         $response->assertJsonFragment([
//             "status" => "success",
//             "message" => "Logged out successfully."
//         ]);
//     }
// }
