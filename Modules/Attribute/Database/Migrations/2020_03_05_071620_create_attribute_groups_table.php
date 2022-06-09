<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeGroupsTable extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_set_id');
            $table->foreign('attribute_set_id')->references('id')->on('attribute_sets')->onDelete('cascade');
            $table->string('name');
            $table->integer('position')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_groups');
    }
}
