<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductAttributeDecimalTable extends Migration
{
    public function up(): void
    {
        Schema::create("product_attribute_decimal", function (Blueprint $table) {
            $table->id();
            $table->double("value");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("product_attribute_decimal");
    }
}
