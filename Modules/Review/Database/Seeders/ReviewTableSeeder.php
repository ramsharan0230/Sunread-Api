<?php

namespace Modules\Review\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReviewTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('reviews')->insert([
            [
                "customer_id" => 1,
                "product_id" => 1,
                "rating" => 2,
                "title" => "Stock Enquiry",
                "description" => "Do you have more stock?",
                "status" => 1,
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }
}
