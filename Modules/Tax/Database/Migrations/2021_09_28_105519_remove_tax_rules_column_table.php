<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveTaxRulesColumnTable extends Migration
{
    public function up(): void
    {
        Schema::table('tax_rules', function (Blueprint $table) {
            
            $table->dropConstrainedForeignId("customer_group_class");
            $table->dropConstrainedForeignId("product_taxable_class");
            $table->dropColumn('status');
            $table->dropColumn('position');
            $table->dropColumn('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('tax_rules', function (Blueprint $table) {
            $table->dropConstrainedForeignId("customer_group_class");
            $table->dropConstrainedForeignId("product_taxable_class");
            $table->dropColumn('status');
            $table->dropColumn('position');
            $table->dropColumn('subtotal');
        });
    }
}
