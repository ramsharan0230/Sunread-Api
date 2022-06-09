<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentMethodMappersTable extends Migration
{
    public function up(): void
    {
        Schema::create('erp_payment_method_mappers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("website_id");
            $table->foreign("website_id")->references("id")->on("websites")->onDelete("restrict");
            $table->string("payment_method");
            $table->string("payment_method_code");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_method_mappers');
    }
}
