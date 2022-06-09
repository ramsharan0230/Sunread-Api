<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSizeChartContentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('size_chart_contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('type');
            $table->json('content');
            $table->unsignedBigInteger('size_chart_id');
            $table->foreign('size_chart_id')->references('id')->on('size_charts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_chart_contents');
    }
}
