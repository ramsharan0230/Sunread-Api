<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatalogInventoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("product_id");
            $table->unsignedBigInteger("website_id");
            $table->decimal("quantity")->nullable();
            $table->boolean("is_in_stock")->nullable();
            $table->boolean("manage_stock")->nullable();
            $table->boolean("use_config_manage_stock")->nullable();

            $table->foreign("product_id")->references("id")->on("products")->onDelete("cascade");
            $table->foreign("website_id")->references("id")->on("websites")->onDelete("cascade");

            $table->unique(["product_id", "website_id"], "inventory_compound");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_inventories');
    }
}
