<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKlarnaOrderTaxesTable extends Migration
{
    public function up(): void
    {
        Schema::create('klarna_order_taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("order_id");
            $table->string("code");
            $table->string("title");
            $table->decimal("percent");
            $table->decimal("amount");

            $table->foreign("order_id")->references("id")->on("klarna_orders")->onDelete("cascade");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klarna_order_taxes');
    }
}
