<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table("roles")->insert([
            [
                "name" => "Administrator",
                "slug" => "super-admin",
                "description" => "Administrator role",
                "permission_type" => "all",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "name" => "Site Administrator",
                "slug" => "basic-admin",
                "description" => "Basic Administrator role",
                "permission_type" => "all",
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }
}
