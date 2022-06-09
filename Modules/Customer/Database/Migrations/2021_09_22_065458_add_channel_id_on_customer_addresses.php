<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChannelIdOnCustomerAddresses extends Migration
{
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->foreign('channel_id')->references('id')->on('channels')->onDelete("set null");
        });
    }

    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn('channel_id');
            $table->dropForeign('channel_id');
        });
    }
}
