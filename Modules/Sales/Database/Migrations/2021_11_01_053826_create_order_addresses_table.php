<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Sales\Entities\OrderAddress;

class CreateOrderAddressesTable extends Migration
{
    public function up(): void
    {
        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("order_id");
            $table->foreign("order_id")->references("id")->on("orders")->onDelete("cascade");


            $table->unsignedBigInteger("customer_id")->nullable();
            $table->foreign("customer_id")->references("id")->on("customers")->onDelete("set null");

            $table->unsignedBigInteger("customer_address_id")->nullable();
            $table->foreign("customer_address_id")->references("id")->on("customer_addresses")->onDelete("set null");

            $table->enum("address_type", OrderAddress::$address_types);

            $table->string("first_name");
            $table->string("middle_name")->nullable();
            $table->string("last_name");
            $table->string("phone");
            $table->string("email");
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('address3')->nullable();
            $table->string("postcode");

            $table->unsignedBigInteger("country_id")->nullable();
            $table->foreign("country_id")->references("id")->on("countries")->onDelete("set null");

            $table->unsignedBigInteger("region_id")->nullable();
            $table->foreign("region_id")->references("id")->on("regions")->onDelete("set null");

            $table->unsignedBigInteger("city_id")->nullable();
            $table->foreign("city_id")->references("id")->on("cities")->onDelete("set null");


            $table->string("region_name")->nullable();
            $table->string("city_name")->nullable();

            $table->string("vat_number")->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('order_addresses');
    }
}
