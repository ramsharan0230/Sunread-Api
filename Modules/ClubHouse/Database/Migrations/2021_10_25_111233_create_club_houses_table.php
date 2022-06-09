<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClubHousesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('club_houses', function (Blueprint $table) {
            $table->id();
            $table->integer('position');
            $table->unsignedBigInteger('website_id');
            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->foreign('website_id')->references('id')->on('websites')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_houses');
    }
}
