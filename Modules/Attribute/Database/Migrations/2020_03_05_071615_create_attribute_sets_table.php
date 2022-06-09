<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeSetsTable extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_user_defined')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_sets');
    }
}
