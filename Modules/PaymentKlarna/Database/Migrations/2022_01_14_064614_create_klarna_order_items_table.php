<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKlarnaOrderItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('klarna_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("order_id");
            $table->foreign("order_id")->references("id")->on("klarna_orders")->onDelete("cascade");

            $table->unsignedBigInteger("website_id")->nullable();
            $table->unsignedBigInteger("channel_id")->nullable();
            $table->unsignedBigInteger("store_id")->nullable();

            $table->unsignedBigInteger("product_id")->nullable();
            $table->foreign("product_id")->references("id")->on("products")->onDelete("set null");

            $table->string("sku");
            $table->string("name");
            $table->decimal("qty");
            $table->decimal("cost");
            $table->decimal("price");
            $table->decimal("row_total");
            $table->decimal("row_total_incl_tax");
            $table->decimal("tax_amount");
            $table->decimal("tax_percent");

            $table->decimal("discount_amount_tax");
            $table->decimal("discount_amount");
            $table->decimal("discount_percent");

            $table->decimal("weight")->nullable();
            $table->decimal("price_incl_tax");
            $table->decimal("row_weight");
            $table->json("product_options");
            $table->string("product_type");

            $table->string("coupon_code")->nullable();

            $table->unique(["product_id", "order_id", "channel_id", "website_id", "store_id"], "product_order_channel_website_store_compound");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klarna_order_items');
    }
}
