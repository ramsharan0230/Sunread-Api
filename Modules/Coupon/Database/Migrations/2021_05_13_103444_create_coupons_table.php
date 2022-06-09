<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string("code")->unique();
            $table->string("name");
            $table->text("description")->nullable();

            $table->date('valid_from');
            $table->date('valid_to');

            $table->decimal("flat_discount_amount")->nullable();
            $table->decimal("min_discount_amount")->nullable();
            $table->decimal("max_discount_amount")->nullable();
            $table->decimal("min_purchase_amount")->nullable();

            $table->float("discount_percent")->nullable();
            $table->integer("max_uses");
            $table->integer("single_user_uses")->default(1);

            $table->boolean("only_new_user")->default(0);
            $table->boolean("scope_public")->default(0);
            $table->boolean("status")->default(1);
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
        Schema::dropIfExists('coupons');
    }
}
