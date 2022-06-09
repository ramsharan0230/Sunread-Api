<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UserDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        $this->call(RolesTableSeeder::class);
        $this->call(AdminsTableSeeder::class);
    }
}
