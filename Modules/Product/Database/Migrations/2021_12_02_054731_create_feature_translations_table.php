<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeatureTranslationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('feature_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('store_id');

            $table->string('name')->nullable();
            $table->text('description')->nullable();

            $table->unique(['feature_id', 'store_id']);
            $table->foreign('feature_id')->references('id')->on('features')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_translations');
    }
}
