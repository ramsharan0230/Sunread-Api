<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('customer_id')->nullable();
            $table->integer('item_count')->nullable()->default(0);
            $table->integer('total_quantity')->nullable()->default(0);
            $table->string('coupon_code')->nullable();
            $table->string('channel_code')->nullable();
            $table->string('store_code')->nullable();
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
        Schema::dropIfExists('carts');
    }
}
