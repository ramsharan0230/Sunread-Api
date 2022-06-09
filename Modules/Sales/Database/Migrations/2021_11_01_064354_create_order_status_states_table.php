<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderStatusStatesTable extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_states', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("state");
            $table->boolean("is_default")->default(0);
            $table->integer("position");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_states');
    }
}
