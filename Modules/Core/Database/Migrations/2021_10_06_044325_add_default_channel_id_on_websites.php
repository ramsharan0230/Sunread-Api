<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultChannelIdOnWebsites extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->unsignedBigInteger("default_channel_id")->nullable();
            $table->foreign("default_channel_id")->references("id")->on("channels")->onDelete("set null");
        });
    }

    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropForeign("default_channel_id");
            $table->dropColumn("default_channel_id");
        });
    }
}
