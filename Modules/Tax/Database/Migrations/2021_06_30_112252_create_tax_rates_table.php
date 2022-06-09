<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxRatesTable extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();

            $table->unsignedbigInteger("country_id")->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');

            $table->unsignedbigInteger("region_id")->nullable();
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');

            $table->string("identifier")->unique();
            $table->boolean("use_zip_range");
            $table->string("zip_code")->nullable();
            $table->string("postal_code_from")->nullable();
            $table->string("postal_code_to")->nullable();
            $table->decimal("tax_rate");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
}
