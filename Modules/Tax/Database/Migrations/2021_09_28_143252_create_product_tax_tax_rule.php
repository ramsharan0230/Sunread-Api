<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTaxTaxRule extends Migration
{
    public function up(): void
    {
        Schema::create('product_tax_group_tax_rule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("product_tax_group_id");
            $table->unsignedBigInteger("tax_rule_id");
          
            $table->foreign("product_tax_group_id")->references("id")->on("product_tax_groups")->onDelete("cascade");
            $table->foreign("tax_rule_id")->references("id")->on("tax_rules")->onDelete("cascade");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_tax_group_tax_rule');
    }
}
