<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErpWebhookLogTable extends Migration
{
    public function up(): void
    {
        Schema::create("erp_webhook_logs", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("website_id")->nullable();
            $table->foreign("website_id")->references("id")->on("websites");
            $table->string("entity_type")->nullable();
            $table->string("entity_id")->nullable();
            $table->json("payload");
            $table->tinyInteger("is_processing")->default(1);
            $table->tinyInteger("status")->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("erp_webhook_logs");
    }
}
