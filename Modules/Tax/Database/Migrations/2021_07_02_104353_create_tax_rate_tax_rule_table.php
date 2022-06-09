<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxRateTaxRuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_rate_tax_rule', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_rate_id');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('cascade');
            $table->unsignedBigInteger('tax_rule_id');
            $table->foreign('tax_rule_id')->references('id')->on('tax_rules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_rate_tax_rules');
    }
}
