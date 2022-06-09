<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTaxGroupsTable extends Migration
{
    public function up(): void
    {
        Schema::create('customer_tax_groups', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->text("description");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_tax_groups');
    }
}
