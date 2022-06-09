<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductImageBackgroundSize extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('background_size')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('', function (Blueprint $table) {

        });
    }
}
