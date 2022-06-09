<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeTranslationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('store_id');

            $table->text('name')->nullable();

            $table->unique(['attribute_id', 'store_id']);
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_translations');
    }
}
