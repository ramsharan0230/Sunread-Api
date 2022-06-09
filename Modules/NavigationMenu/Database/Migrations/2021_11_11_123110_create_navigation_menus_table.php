<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNavigationMenusTable extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_menus', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->tinyInteger('status')->default(1);
            $table->string('location')->nullable();
            $table->unsignedBigInteger('website_id');
            $table->timestamps();

            $table->foreign('website_id')->references('id')->on('websites')->onUpdate('cascade')->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_menus');
    }
}
