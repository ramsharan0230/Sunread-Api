<?php


namespace Tests;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Modules\User\Entities\Role;
use Modules\User\Entities\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;


class AuthTestCase extends TestCase
{
    use RefreshDatabase;

    protected  $headers;
    public function setUp():void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    public function createAdmin($attributes = [])
    {

        $password = isset($attributes['password'])? $attributes['password']: 'password';
        $admin_slug = isset($attributes['slug'])? $attributes['slug']: 'super-admin';
        $role = Role::where('slug', $admin_slug)->first();
        $attributes = [
            'password' => Hash::make($password),
            'role_id' => $role->id
        ];
        $admin = Admin::factory()->make($attributes);
        $token =  $this->createToken($admin->email, $password);
        $this->headers['Authorization'] = 'Bearer '.$token;
        return $admin;
    }

    private function createToken($email, $password)
    {
        $jwtToken = null;
        $admin_jwt_ttl = config('jwt.admin_jwt_ttl');
        if (!$jwtToken = Auth::guard('admin')->setTTl($admin_jwt_ttl)->attempt(['email' => $email, 'password' => $password])) {
             return false;
        }
        return $jwtToken;

    }

}
