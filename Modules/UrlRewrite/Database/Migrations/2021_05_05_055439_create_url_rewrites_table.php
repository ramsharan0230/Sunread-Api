<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUrlRewritesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('url_rewrites', function (Blueprint $table) {
            $table->id();
            $table->string("type")->nullable();
            $table->json("type_attributes")->nullable();
            $table->string("request_path")->index();
            $table->string("target_path");
            $table->smallInteger("redirect_type")->default(0);
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
        Schema::dropIfExists('url_rewrites');
    }
}
