<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Erp\Entities\ErpLog;

class CreateErpLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create("erp_logs", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("website_id")->nullable();
            $table->foreign("website_id")->references("id")->on("websites");

            $table->string("entity_type")->nullable();
            $table->string("entity_id")->nullable();
            $table->enum("causer_type", ErpLog::$causerTypes)->default(ErpLog::SYSTEM);
            $table->unsignedBigInteger("causer_id")->nullable();
            $table->foreign("causer_id")->references("id")->on("admins");

            $table->string("event");
            $table->string("resoponse_code");
            $table->json("request");
            $table->json("response");
            $table->string("type");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("erp_logs");
    }
}
