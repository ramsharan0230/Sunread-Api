<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCompositeKeyErpPaymentMethodMappersTable extends Migration
{
    public function up(): void
    {
        Schema::table('erp_payment_method_mappers', function (Blueprint $table) {
            $table->unique(["website_id", "payment_method_code"], "website_payment_method_code_unique");
        });
    }

    public function down(): void
    {
        Schema::table('erp_payment_method_mappers', function (Blueprint $table) {
            $table->dropUnique("website_payment_method_code_unique");
        });
    }
}
