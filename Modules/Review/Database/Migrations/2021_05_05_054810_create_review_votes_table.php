<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("customer_id")->nullable();
            $table->unsignedBigInteger("review_id")->nullable();
            $table->unique(['customer_id', 'review_id'], "review_vote_compound_unique");
            $table->foreign("customer_id")->references("id")->on("customers")->onDelete('set null');
            $table->foreign("review_id")->references("id")->on("reviews")->onDelete('set null');
            $table->boolean('vote_type');
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
        Schema::dropIfExists('review_votes');
    }
}
