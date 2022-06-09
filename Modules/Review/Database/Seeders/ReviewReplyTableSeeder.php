<?php

namespace Modules\Review\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReviewReplyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('review_replies')->insert([
            [
                "review_id" => 1,
                "description" => "We will Contact soon.",
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }
}
