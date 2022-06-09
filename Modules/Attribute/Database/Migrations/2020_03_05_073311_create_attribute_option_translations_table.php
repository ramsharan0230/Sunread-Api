<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeOptionTranslationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_option_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_option_id');
            $table->foreign('attribute_option_id')->references('id')->on('attribute_options')->onDelete('cascade');
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->text('name')->nullable();
            $table->unique(['attribute_option_id', 'store_id'], 'attribute_option_store_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_option_translations');
    }
}
