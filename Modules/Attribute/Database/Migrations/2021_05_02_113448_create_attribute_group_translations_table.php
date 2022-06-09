<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeGroupTranslationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_group_translations', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('attribute_group_id');
            $table->foreign('attribute_group_id')->references('id')->on('attribute_groups')->onDelete('cascade');
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->text('name')->nullable();
            $table->unique(['attribute_group_id', 'store_id'], 'attribute_group_store_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_group_translations');
    }
}
