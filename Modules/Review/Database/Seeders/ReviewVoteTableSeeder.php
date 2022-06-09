<?php

namespace Modules\Review\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReviewVoteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('review_votes')->insert([
            [
                "customer_id" => 1,
                "review_id" => 1,
                "vote_type" => 1,
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }
}
