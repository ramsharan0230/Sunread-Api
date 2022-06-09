<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeGroupAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_group_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_group_id');
            $table->foreign('attribute_group_id')->references('id')->on('attribute_groups')->onDelete('cascade');
            $table->unsignedBigInteger('attribute_id');
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');

            $table->unique(["attribute_id", "attribute_group_id"], "attribute_compound_unique");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attribute_group_attributes');
    }
}
