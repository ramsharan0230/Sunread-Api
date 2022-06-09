<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributesTable extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('type');
            $table->string('scope');
            $table->string('validation')->nullable();
            $table->boolean('is_required')->default(0);
            $table->boolean('is_unique')->default(0);
            $table->boolean('comparable_on_storefront')->default(0);
            $table->boolean('is_searchable')->default(0);
            $table->integer('search_weight')->nullable();
            $table->boolean('is_user_defined')->default(1);
            $table->boolean('is_visible_on_storefront')->default(0);
            $table->boolean('use_in_layered_navigation')->default(0);
            $table->integer('position')->nullable();
            $table->string('default_value')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
}
