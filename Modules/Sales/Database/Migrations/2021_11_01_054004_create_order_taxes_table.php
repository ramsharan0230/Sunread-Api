<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTaxesTable extends Migration
{
    public function up(): void
    {
        Schema::create('order_taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("order_id");
            $table->foreign("order_id")->references("id")->on("orders")->onDelete("cascade");

            $table->string("code");
            $table->string("title");
            $table->decimal("percent");
            $table->decimal("amount");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_taxes');
    }
}
