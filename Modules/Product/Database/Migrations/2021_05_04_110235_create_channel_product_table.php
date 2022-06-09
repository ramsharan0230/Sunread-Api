<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelProductTable extends Migration
{
    public function up(): void
    {
        Schema::create("channel_product", function (Blueprint $table) {
            $table->unsignedBigInteger("channel_id");
            $table->foreign("channel_id")->references("id")->on("channels")->onDelete("cascade");
            $table->unsignedBigInteger("product_id");
            $table->foreign("product_id")->references("id")->on("products")->onDelete("cascade");
            $table->boolean("status")->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("channel_product");
    }
}
