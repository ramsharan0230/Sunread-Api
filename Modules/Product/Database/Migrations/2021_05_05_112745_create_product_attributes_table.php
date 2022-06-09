<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductAttributesTable extends Migration
{
    public function up(): void
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("attribute_id");
            $table->foreign("attribute_id")->references("id")->on("attributes")->onDelete("cascade");
            
            $table->unsignedBigInteger("product_id");
            $table->foreign("product_id")->references("id")->on("products")->onDelete("cascade");

            $table->string('scope');
            $table->unsignedBigInteger('scope_id');

            $table->string("value_type");
            $table->unsignedBigInteger("value_id")->nullable();

            $table->unique(["attribute_id", "scope", "scope_id", "product_id", "value_type"], "attribute_compound_unique");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
}
