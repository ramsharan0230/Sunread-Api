<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_lock')->default(0);
            $table->string('password')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('tax_number')->nullable();

            $table->bigInteger('customer_group_id')->unsigned()->nullable();
            $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('set null');

            $table->boolean('subscribed_to_news_letter')->default(0);
            $table->rememberToken();
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
        Schema::dropIfExists('customers');
    }
}
