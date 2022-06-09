<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateErpShippingAttributeMappersColumn extends Migration
{
    public function up(): void
    {
        Schema::table('erp_shipping_attribute_mappers', function (Blueprint $table) {
            $table->string("shipping_method_code")->after('shipping_agent_service_code');
            $table->unique(["website_id", "shipping_agent_code"], "website_shipping_agent_code_unique");
        });
    }

    public function down(): void
    {
        Schema::table('erp_shipping_attribute_mappers', function (Blueprint $table) {
            $table->dropColumn("shipping_method_code");
            $table->dropUnique("website_shipping_agent_code_unique");
        });
    }
}
