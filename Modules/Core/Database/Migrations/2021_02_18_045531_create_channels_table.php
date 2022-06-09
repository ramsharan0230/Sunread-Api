<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelsTable extends Migration
{
    public function up(): void
    {
        Schema::create("channels", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("code");
            $table->string("hostname")->nullable();
            $table->text("description")->nullable();

            $table->unsignedBigInteger("default_store_id")->nullable();
            $table->unsignedBigInteger("website_id");

            $table->foreign("default_store_id")->references("id")->on("stores")->onDelete("cascade");
            $table->foreign("website_id")->references("id")->on("websites")->onDelete("cascade");

            $table->boolean("status")->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("channels");
    }
}
