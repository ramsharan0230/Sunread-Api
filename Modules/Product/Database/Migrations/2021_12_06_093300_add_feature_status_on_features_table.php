<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeatureStatusOnFeaturesTable extends Migration
{
    public function up(): void
    {
        Schema::table("features", function (Blueprint $table) {
            $table->boolean("status")->default(1)->after("description");
        });
    }

    public function down(): void
    {
        Schema::table("features", function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
