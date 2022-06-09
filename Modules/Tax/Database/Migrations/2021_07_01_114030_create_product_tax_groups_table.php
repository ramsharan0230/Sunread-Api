<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTaxGroupsTable extends Migration
{
    public function up(): void
    {
        Schema::create('product_tax_groups', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->text("description");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_tax_groups');
    }
}
