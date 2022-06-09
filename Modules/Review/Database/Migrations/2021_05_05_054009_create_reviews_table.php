<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("customer_id")->nullable();
            $table->foreign("customer_id")->references("id")->on("customers")->onDelete('set null');
            $table->unsignedBigInteger("product_id")->nullable();
            $table->foreign("product_id")->references("id")->on("products")->onDelete('set null');
            $table->tinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('reviews');
    }
}
