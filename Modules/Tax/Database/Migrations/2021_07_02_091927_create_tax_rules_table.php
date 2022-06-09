<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_group_class');
            $table->foreign('customer_group_class')->references('id')->on('customer_tax_groups')->onDelete('cascade');
            $table->unsignedBigInteger('product_taxable_class');
            $table->foreign('product_taxable_class')->references('id')->on('product_tax_groups')->onDelete('cascade');
            $table->string('name');
            $table->integer('position');
            $table->integer('priority');
            $table->decimal('subtotal')->nullable();
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('tax_rules');
    }
}
