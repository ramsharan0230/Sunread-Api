<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductAlertStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_alert_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable();
            $table->string('email_address')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('store_id');
            $table->boolean('send_count')->default(0);
            $table->date('send_date')->nullable();
            $table->boolean('status')->default(0);

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

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
        Schema::dropIfExists('product_alert_stocks');
    }
}
