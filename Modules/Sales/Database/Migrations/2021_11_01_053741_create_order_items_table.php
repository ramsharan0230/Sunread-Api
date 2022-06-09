<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Product\Entities\Product;

class CreateOrderItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("website_id")->nullable();
            $table->unsignedBigInteger("channel_id")->nullable();
            $table->unsignedBigInteger("store_id")->nullable();

            $table->unsignedBigInteger("product_id")->nullable();
            $table->foreign("product_id")->references("id")->on("products")->onDelete("set null");

            $table->unsignedBigInteger("order_id");
            $table->foreign("order_id")->references("id")->on("orders")->onDelete("cascade");

            $table->json("product_options");
            $table->enum("product_type", Product::$product_types);

            $table->string("sku");
            $table->string("name");
            $table->decimal("weight")->nullable();
            $table->decimal("qty");
            $table->decimal("cost");
            $table->decimal("price");
            $table->decimal("price_incl_tax");

            $table->string("coupon_code")->nullable();
            $table->decimal("discount_amount")->nullable();
            $table->decimal("discount_percent")->nullable();
            $table->decimal("discount_amount_tax")->nullable();
            $table->decimal("tax_amount");
            $table->decimal("tax_percent");
            $table->decimal("row_total");
            $table->decimal("row_total_incl_tax");
            $table->decimal("row_weight");

            $table->unique(["product_id", "order_id", "channel_id", "website_id", "store_id"], "product_order_channel_website_store_compound");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
}
