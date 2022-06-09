<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTransactionLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('order_transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("order_id");

            $table->decimal("amount");
            $table->string("currency");
            $table->string("ip_address")->nullable();
            $table->json("request")->nullable();
            $table->json("response");
            $table->string("response_code", 20);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_transaction_logs');
    }
}
