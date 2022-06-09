<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOrderTableForeginKey extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign("billing_address_id")->references("id")->on("order_addresses")->onDelete("set null");
            $table->foreign("shipping_address_id")->references("id")->on("order_addresses")->onDelete("set null");
            $table->foreign("status")->references("slug")->on("order_statuses");
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign("billing_address_id")->references("id")->on("order_addresses")->onDelete("set null");
            $table->foreign("shipping_address_id")->references("id")->on("order_addresses")->onDelete("set null");
            $table->foreign("status")->references("slug")->on("order_statuses");
        });
    }
}
