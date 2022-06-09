<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductAttributeStringTable extends Migration
{
    public function up(): void
    {
        Schema::create("product_attribute_string", function (Blueprint $table) {
            $table->id();
            $table->string("value");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("product_attribute_string");
    }
}
