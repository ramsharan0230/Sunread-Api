<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Sales\Entities\OrderComment;

class AddColumnStatusFlagOnComment extends Migration
{
    public function up(): void
    {
        Schema::table("order_comments", function (Blueprint $table) {
            $table->enum("status_flag", OrderComment::$status_flags)->default(OrderComment::STATUS_INFO)->after("comment");
        });
    }

    public function down(): void
    {
        Schema::table("order_comments", function (Blueprint $table) {
            $table->dropColumn("status_flag");
        });
    }
}
