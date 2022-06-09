<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNavigationMenuItemsTable extends Migration
{

    public function up(): void
    {
        Schema::create('navigation_menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('navigation_menu_id');
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('navigation_menu_id')->references('id')->on('navigation_menus')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_menu_items');
    }
}
