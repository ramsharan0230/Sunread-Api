<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKlarnaOrderTaxItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('klarna_order_tax_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("tax_id");
            $table->unsignedBigInteger("item_id")->nullable();
            $table->decimal("percent");
            $table->decimal("amount");

            $table->foreign("tax_id")->references("id")->on("klarna_order_taxes")->onDelete("cascade");
            $table->foreign("item_id")->references("id")->on("products")->onDelete("set null");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klarna_order_tax_items');
    }
}
