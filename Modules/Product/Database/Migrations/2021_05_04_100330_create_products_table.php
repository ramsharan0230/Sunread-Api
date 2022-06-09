<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    public function up(): void
    {
        Schema::create("products", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("parent_id")->nullable();
            $table->unsignedBigInteger("website_id");
            
            $table->foreign("website_id")->references("id")->on("websites")->onDelete("cascade");
            $table->foreign("parent_id")->references("id")->on("products")->onDelete("cascade");
            $table->unsignedBigInteger("brand_id")->nullable();
            $table->foreign("brand_id")->references("id")->on("brands")->onDelete("restrict");
            $table->unsignedBigInteger("attribute_set_id")->nullable();
            $table->foreign("attribute_set_id")->references("id")->on("attribute_sets")->onDelete("cascade");

            $table->string("sku")->unique()->nullable();
            $table->string("type")->default("simple");
            $table->boolean("status")->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("products");
    }
}
