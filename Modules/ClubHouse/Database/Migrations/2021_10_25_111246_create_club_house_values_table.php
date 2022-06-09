<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClubHouseValuesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('club_house_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_house_id');
            $table->string('scope');
            $table->unsignedBigInteger('scope_id');
            $table->string('attribute');
            $table->string('value')->nullable();
            $table->timestamps();

            $table->foreign('club_house_id')->references('id')->on('club_houses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_house_values');
    }
}
