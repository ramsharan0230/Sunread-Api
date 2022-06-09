<?php

namespace Modules\Review\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ReviewDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(ReviewTableSeeder::class);
        $this->call(ReviewVoteTableSeeder::class);
        $this->call(ReviewReplyTableSeeder::class);
    }
}
