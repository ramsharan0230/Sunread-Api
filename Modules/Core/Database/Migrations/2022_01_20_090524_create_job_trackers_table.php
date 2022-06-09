<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobTrackersTable extends Migration
{
    public function up(): void
    {
        Schema::create('job_trackers', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->integer("total_jobs")->nullable();
            $table->integer("completed_jobs")->nullable();
            $table->integer("failed_jobs")->nullable();
            $table->integer("status")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_trackers');
    }
}
