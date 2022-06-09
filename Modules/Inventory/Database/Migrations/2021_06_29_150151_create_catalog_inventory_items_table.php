<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatalogInventoryItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("catalog_inventory_id")->nullable();
            $table->unsignedBigInteger("order_id")->nullable();
            $table->unsignedBigInteger("adjusted_by")->nullable();
            $table->decimal("quantity");
            $table->string("event");
            $table->string("adjustment_type");

            $table->foreign("adjusted_by")->references("id")->on("admins");
            $table->foreign("catalog_inventory_id")->references("id")->on("catalog_inventories")->onDelete("set null");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_inventory_items');
    }
}
