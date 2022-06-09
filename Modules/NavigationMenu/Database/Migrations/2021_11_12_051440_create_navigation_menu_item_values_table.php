<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNavigationMenuItemValuesTable extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_menu_item_values', function (Blueprint $table) {
            $table->id();
            $table->string('scope');
            $table->unsignedBigInteger('navigation_menu_item_id');
            $table->unsignedBigInteger('scope_id');
            $table->string('attribute');
            $table->longText('value')->nullable();
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('navigation_menu_item_id')->references('id')->on('navigation_menu_items')->onDelete('cascade');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('navigation_menu_item_values');
    }
}
