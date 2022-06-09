<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Page\Entities\Page;

class AddLayoutTypeOnPagesTable extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->enum('layout_type', Page::$layout_types)->default(Page::LAYOUT_TYPE_DARK);
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn("layout_type");
        });
    }
}
