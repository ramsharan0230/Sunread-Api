<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminsTableSeeder extends Seeder
{
    public function run(): void
    {
        $default_users = [
            [
                "first_name" => "Admin",
                "last_name" => "Example",
                "email" => "admin@example.com",
            ], [
                "first_name" => "Vivek",
                "last_name" => "Ghimire",
                "email" => "vivek@admin.com",
            ], [
                "first_name" => "Dinesh",
                "last_name" => "Ghimire",
                "address" => "Nepal",
                "email" => "dinesh@hbgdesignlab.se",
            ], [
                "first_name" => "Oskar",
                "last_name" => "Jönsson",
                "email" => "oskarj@admin.com",
            ], [
                "first_name" => "Oskar",
                "last_name" => "Hertzman",
                "email" => "oskarh@admin.com",
            ], [
                "first_name" => "Pierre",
                "last_name" => "Gronberg",
                "company" => "hdl",
                "email" => "pierre@hbgdesignlab.se",
            ], [
                "first_name" => "Bijay",
                "last_name" => "Luitel",
                "company" => "hdl",
                "email" => "luitelbj@gmail.com",
                "password" => "Alice123$"
            ], [
                "first_name" => "Dinesh",
                "last_name" => "Parajapati",
                "company" => "hdl",
                "email" => "dinpra@gmail.com",
                "password" => "123456"
            ]
        ];

        $data = array_map(function($user) {
            return [
                "first_name" => $user["first_name"] ?? "Admin",
                "last_name" => $user["last_name"] ?? "example",
                "email" => $user["email"] ?? "admin@example.com",
                "password" => isset($user["password"]) ? bcrypt($user["password"]) : bcrypt("admin123"),
                "api_token" => Str::random(80),
                "status" => 1,
                "role_id" => 1,
                "company" => $user["company"] ?? "abc.co",
                "address" => $user["address"] ?? "sweden",
                "created_at" => now(),
                "updated_at" => now()
            ];
        }, $default_users);

        DB::table("admins")->insert($data);
    }
}
