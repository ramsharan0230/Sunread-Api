<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCompositeKeysFromPageAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('page_attributes', function (Blueprint $table) {
            $table->index('page_id');
            $table->dropUnique([ 'page_id', 'attribute' ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('page_attributes', function (Blueprint $table) {
            
        });
    }
}
