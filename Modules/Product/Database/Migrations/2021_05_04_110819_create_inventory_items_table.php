<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create("inventory_items", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("channel_id");
            $table->foreign("channel_id")->references("id")->on("channels")->onDelete("cascade");
            $table->unsignedBigInteger("product_id");
            $table->foreign("product_id")->references("id")->on("products")->onDelete("cascade");

            $table->integer("stock");
            $table->boolean("status")->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("inventory_items");
    }
}
