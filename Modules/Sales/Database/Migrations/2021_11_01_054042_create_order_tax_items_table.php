<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTaxItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('order_tax_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("tax_id");
            $table->foreign("tax_id")->references("id")->on("order_taxes")->onDelete("cascade");

            $table->unsignedBigInteger("item_id")->nullable();
            $table->foreign("item_id")->references("id")->on("products")->onDelete("set null");

            $table->decimal("tax_percent");
            $table->decimal("amount");
            $table->string("tax_item_type");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tax_items');
    }
}
