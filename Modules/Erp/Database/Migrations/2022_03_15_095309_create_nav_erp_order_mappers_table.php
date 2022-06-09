<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNavErpOrderMappersTable extends Migration
{
    public function up(): void
    {
        Schema::create("nav_erp_order_mappers", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("website_id"); 
            $table->foreign("website_id")->references("id")->on("websites")->onDelete("cascade");
            $table->string("title");
            $table->unsignedBigInteger("country_id")->nullable();
            $table->foreign("country_id")->references("id")->on("countries");
            $table->string("nav_customer_number");
            $table->string("shipping_account");
            $table->string("discount_account");
            $table->string("customer_price_group");
            $table->boolean("is_default")->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("nav_erp_order_mappers");
    }
}
