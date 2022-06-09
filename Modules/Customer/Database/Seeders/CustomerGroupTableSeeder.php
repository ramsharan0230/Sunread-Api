<?php

namespace Modules\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Tax\Entities\CustomerTaxGroup;

class CustomerGroupTableSeeder extends Seeder
{
    public function run(): void
    {
        $customer_tax_group = CustomerTaxGroup::factory()->create();
        DB::table("customer_groups")->insert([
            "slug" => "general",
            "name" => "General",
            "customer_tax_group_id" => $customer_tax_group->id,
            "is_user_defined" => 0,
            "created_at" => now(),
            "updated_at" => now(),
        ],
        [
            "slug" => "not_logged_in",
            "name" => "Not Logged In",
            "customer_tax_group_id" => $customer_tax_group->id,
            "is_user_defined" => 0,
            "created_at" => now(),
            "updated_at" => now(),
        ]);
    }
}