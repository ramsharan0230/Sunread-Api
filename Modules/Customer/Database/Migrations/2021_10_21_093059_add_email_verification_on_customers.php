<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailVerificationOnCustomers extends Migration
{

    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('verification_token')->nullable();
            $table->boolean('is_email_verified')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['verification_token', 'is_email_verified']);
        });
    }
}
