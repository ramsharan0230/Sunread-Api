<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSizeChartsTable extends Migration
{
    public function up(): void
    {
        Schema::create('size_charts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('content')->nullable();
            $table->boolean('status')->default(1);
            $table->unsignedBigInteger("website_id")->nullable();
            $table->foreign("website_id")->references("id")->on("websites")->onDelete("cascade");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_charts');
    }
}
