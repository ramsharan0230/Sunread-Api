<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPositionOnAttributeGroupAttributes extends Migration
{
    public function up(): void
    {
        Schema::table('attribute_group_attributes', function (Blueprint $table) {
            $table->integer('position')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('attribute_group_attributes', function (Blueprint $table) {

        });
    }
}
