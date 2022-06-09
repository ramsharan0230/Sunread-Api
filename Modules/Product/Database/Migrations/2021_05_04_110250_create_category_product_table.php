<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryProductTable extends Migration
{
    public function up(): void
    {
        Schema::create("category_product", function (Blueprint $table) {
            $table->unsignedBigInteger("category_id");
            $table->foreign("category_id")->references("id")->on("categories")->onDelete("cascade");
            $table->unsignedBigInteger("product_id");
            $table->foreign("product_id")->references("id")->on("products")->onDelete("cascade");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("category_product");
    }
}
