<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingAttributeMappersTable extends Migration
{
    public function up(): void
    {
        Schema::create('erp_shipping_attribute_mappers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("website_id");
            $table->string("shipping_agent_code");
            $table->string("shipping_agent_service_code");
            $table->foreign("website_id")->references("id")->on("websites")->onDelete("restrict");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_attribute_mappers');
    }
}
