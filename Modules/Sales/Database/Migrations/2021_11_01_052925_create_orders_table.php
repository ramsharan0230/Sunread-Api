<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use phpDocumentor\Reflection\Types\Nullable;

class CreateOrdersTable extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("website_id")->nullable();
            $table->unsignedBigInteger("channel_id")->nullable();
            $table->unsignedBigInteger("store_id")->nullable();

            $table->string("website_name");
            $table->string("channel_name");
            $table->string("store_name");

            $table->uuid("cart_id");
            $table->string("external_erp_id")->nullable();

            $table->unsignedBigInteger("customer_id")->nullable();
            $table->foreign("customer_id")->references("id")->on("customers")->onDelete("set null");

            $table->boolean("is_guest")->default(0);

            $table->unsignedBigInteger("billing_address_id")->nullable();

            $table->unsignedBigInteger("shipping_address_id")->nullable();

            $table->string("shipping_method")->nullable();
            $table->string("shipping_method_label")->nullable();

            $table->string("payment_method")->nullable();
            $table->string("payment_method_label")->nullable();

            $table->string("currency_code");
            $table->string("coupon_code")->nullable();
            $table->decimal("discount_amount")->nullable();
            $table->decimal("discount_amount_tax")->nullable();
            $table->decimal("shipping_amount")->nullable();
            $table->decimal("shipping_amount_tax")->nullable();
            $table->decimal("sub_total");
            $table->decimal("sub_total_tax_amount");
            $table->decimal("tax_amount");
            $table->decimal("grand_total");
            $table->decimal("weight")->nullable();

            $table->decimal("total_items_ordered");
            $table->decimal("total_qty_ordered");

            $table->string("customer_email")->nullable();
            $table->string("customer_first_name")->nullable();
            $table->string("customer_middle_name")->nullable();
            $table->string("customer_last_name")->nullable();
            $table->string("customer_phone")->nullable();
            $table->string("customer_taxvat")->nullable();
            $table->string("customer_ip_address")->nullable();
            $table->string("status");
            $table->unique(["cart_id", "channel_id", "website_id", "store_id"], "cart_channel_website_store_compound");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}
