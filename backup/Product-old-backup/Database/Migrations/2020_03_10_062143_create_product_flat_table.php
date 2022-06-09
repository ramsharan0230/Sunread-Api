<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductFlatTable extends Migration
{   public function up()
{
    Schema::create('product_flat', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('sku')->nullable();
        $table->string('slug')->nullable();
        $table->string('name')->nullable();
        $table->string('description')->nullable();
        $table->boolean('new')->nullable();
        $table->boolean('featured')->nullable();
        $table->boolean('status')->nullable();
        $table->text('thumbnail')->nullable();
        $table->decimal('price', 18, 4)->nullable();
        $table->decimal('cost', 18, 4)->nullable();


        $table->decimal('special_price',18,4)->nullable();
        $table->date('special_price_from')->nullable();
        $table->date('special_price_to')->nullable();

        $table->decimal('weight', 12, 4)->nullable();
        $table->integer('color')->nullable();
        $table->string('color_label')->nullable();
        $table->integer('size')->nullable();
        $table->string('size_label')->nullable();

        $table->string('locale')->nullable();

        $table->bigInteger('product_id')->unsigned()->nullable();
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

        $table->bigInteger('parent_id')->unsigned()->nullable();

        $table->foreign('parent_id')->references('id')->on('product_flat')->onDelete('cascade');


        $table->text('short_description')->nullable();
        $table->text('meta_title')->nullable();
        $table->text('meta_keywords')->nullable();
        $table->text('meta_description')->nullable();
        $table->decimal('width', 12, 4)->nullable();
        $table->decimal('height', 12, 4)->nullable();
        $table->decimal('depth', 12, 4)->nullable();
        $table->timestamps();


    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_flat');
    }


}
