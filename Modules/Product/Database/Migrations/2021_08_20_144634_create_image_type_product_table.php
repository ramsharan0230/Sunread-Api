<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImageTypeProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_type_product_image', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("image_type_id");
            $table->unsignedBigInteger("product_image_id");
          
            $table->foreign("image_type_id")->references("id")->on("image_types")->onDelete("cascade");
            $table->foreign("product_image_id")->references("id")->on("product_images")->onDelete("cascade");

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
        Schema::dropIfExists('image_type_product_image');
    }
}
