<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSizeChartScopesTable extends Migration
{
    public function up(): void
    {
        Schema::create('size_chart_scopes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('size_chart_id');
            $table->foreign('size_chart_id')->references('id')->on('size_charts')->onDelete('cascade');
            $table->string('scope');
            $table->unsignedBigInteger('scope_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_chart_scopes');
    }
}
