<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();
            $table->string("causer_type");
            $table->unsignedBigInteger("causer_id")->nullable()->comment('admin id');
            $table->unsignedBigInteger("order_id");
            $table->string("title");
            $table->json("data");
            $table->string("action")->comment("event name");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_logs');
    }
}
