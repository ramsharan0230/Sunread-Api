<?php

namespace Modules\Sales\Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Sales\Entities\OrderStatusState;

class OrderStatusTableSeeder extends Seeder
{
    public function run(): void
    {
        $state_statuses = ["pending", "processing", "holded", "completed", "closed", "cancelled"];

        $states = OrderStatusState::all();
        foreach ($state_statuses as $key => $status) {
            $data[] = [
                "name" => $status,
                "slug" => Str::slug($status),
                "state_id" => $states[$key]->id,
                "created_at" => now()
            ];
        }

        DB::table("order_statuses")->insert($data);
    }
}
