<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailNotificationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('email_notifications', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("subject");
            $table->longText("html_content");
            $table->string("recipient_email_address");
            $table->string("recipient_user_type")->nullable();
            $table->string("recipient_user_id")->nullable();
            $table->integer("email_template_id");
            $table->string("email_template_code");
            $table->boolean("is_sent")->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_notifications');
    }
}
