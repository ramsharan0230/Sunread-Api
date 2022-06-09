<?php

namespace Modules\Sales\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Sales\Entities\OrderComment;
use Modules\User\Entities\Admin;

class OrderCommentTableSeeder extends Seeder
{
    public function run()
    {
        OrderComment::factory()->create([
            "user_id" => Admin::first()->id
        ]);
    }
}
