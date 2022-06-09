<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();

            $table->string('address1');
            $table->string('address2')->nullable();
            $table->string('address3')->nullable();

            $table->string('postcode');
            $table->string('phone');
            $table->string('vat_number')->nullable();

            $table->boolean('default_billing_address')->default(0);
            $table->boolean('default_shipping_address')->default(0);
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
        Schema::dropIfExists('customer_addresses');
    }
}
