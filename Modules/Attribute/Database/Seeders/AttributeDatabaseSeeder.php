<?php

namespace Modules\Attribute\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AttributeDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $this->call(AttributeSetTableSeeder::class);
        $this->call(AttributeTableSeeder::class);
        $this->call(AttributeGroupTableSeeder::class);
        $this->call(AttributeOptionTableSeeder::class);
    }
}
