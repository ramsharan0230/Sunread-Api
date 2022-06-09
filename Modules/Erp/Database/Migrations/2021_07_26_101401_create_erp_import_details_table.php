<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErpImportDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_import_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("erp_import_id"); 
            $table->foreign('erp_import_id')->references('id')->on('erp_imports')->onDelete('cascade');
            $table->string("sku");
            $table->json("value");
            $table->string("hash")->nullable();
            $table->boolean("status")->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_import_details');
    }
}
