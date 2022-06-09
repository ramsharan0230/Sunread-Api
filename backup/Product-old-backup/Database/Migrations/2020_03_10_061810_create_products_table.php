<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sku')->unique();
            $table->string('slug')->unique();
            $table->string('type');
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('attribute_family_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('attribute_family_id')->references('id')->on('attribute_families')->onDelete('restrict');
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
    }
}
