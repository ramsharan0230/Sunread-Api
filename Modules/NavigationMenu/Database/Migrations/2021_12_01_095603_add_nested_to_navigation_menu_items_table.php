<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNestedToNavigationMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up(): void
    {
        Schema::table('navigation_menu_items', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('navigation_menu_id');
            $table->nestedSet();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('navigation_menu_items', function (Blueprint $table) {
            $table->dropColumn('position');
            $table->dropNestedSet();
        });
    }
}
