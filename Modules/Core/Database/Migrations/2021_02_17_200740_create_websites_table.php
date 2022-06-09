<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebsitesTable extends Migration
{
    public function up(): void
    {
        Schema::create("websites", function (Blueprint $table) {
            $table->id();
            $table->string("code");
            $table->string("hostname");
            $table->string("name");
            $table->text("description")->nullable();
            $table->boolean("status")->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("websites");
    }
}
