<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerTaxGroupsOnCustomerGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_tax_group_id')->nullable();
            $table->foreign('customer_tax_group_id')->references('id')->on('customer_tax_groups')->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->dropColumn('customer_tax_group_id');
            $table->dropForeign('customer_tax_group_id');
        });
    }
}
