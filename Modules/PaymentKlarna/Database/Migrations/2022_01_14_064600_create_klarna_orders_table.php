<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKlarnaOrdersTable extends Migration
{
    public function up(): void
    {
        Schema::create('klarna_orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("order_id")->nullable();
            $table->foreign("order_id")->references("id")->on("orders")->onDelete("cascade");

            $table->uuid("cart_id")->nullable();

            $table->unsignedBigInteger("website_id")->nullable();
            $table->unsignedBigInteger("channel_id")->nullable();
            $table->string("store_name");
            $table->unsignedBigInteger("store_id")->nullable();

            $table->unsignedBigInteger("customer_id")->nullable();
            $table->foreign("customer_id")->references("id")->on("customers")->onDelete("set null");

            $table->string("klarna_api_order_id")->nullable();

            $table->string("currency_code");
            $table->decimal("sub_total")->nullable();
            $table->decimal("grand_total")->nullable();
            $table->decimal("total_qty_ordered")->nullable();
            $table->decimal("total_item_ordered")->nullable();
            $table->string("customer_ip_address")->nullable();
            $table->decimal("shipping_amount")->nullable();
            $table->decimal("shipping_amount_tax")->nullable();

            $table->string("coupon_code")->nullable();
            $table->decimal("discount_amount")->nullable();
            $table->decimal("discount_amount_tax")->nullable();

            $table->decimal("tax_amount")->nullable();
            $table->string("shipping_method")->nullable();
            $table->string("shipping_method_label")->nullable();
            $table->json("klarna_response")->nullable();

            $table->decimal("sub_total_tax_amount");
            $table->decimal("weight")->nullable();

            $table->string("base_url")->nullable();
            $table->string("status")->nullable();

            $table->unique(["cart_id", "channel_id", "website_id", "store_id"], "cart_channel_website_store_compound");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klarna_orders');
    }
}
