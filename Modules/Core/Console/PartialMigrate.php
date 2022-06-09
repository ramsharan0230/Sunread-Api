<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PartialMigrate extends Command
{
    protected $signature = 'partial:migrate';

    protected $description = 'This command will run all updated seeders.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): bool
    {
        Schema::disableForeignKeyConstraints();

        DB::table('attributes')->truncate();
        DB::table('attribute_configurable_products')->truncate();
        DB::table('attribute_groups')->truncate();
        DB::table('attribute_group_attributes')->truncate();
        DB::table('attribute_group_translations')->truncate();
        DB::table('attribute_options')->truncate();
        DB::table('attribute_option_translations')->truncate();
        DB::table('attribute_sets')->truncate();
        DB::table('attribute_translations')->truncate();

        DB::table('products')->truncate();
        DB::table('attribute_configurable_products')->truncate();
        DB::table('attribute_options_child_products')->truncate();
        DB::table('category_product')->truncate();
        DB::table('channel_product')->truncate();
        DB::table('product_alert_stocks')->truncate();
        DB::table('image_type_product_image')->truncate();
        DB::table('product_attributes')->truncate();
        DB::table('product_attribute_boolean')->truncate();
        DB::table('product_attribute_decimal')->truncate();
        DB::table('product_attribute_integer')->truncate();
        DB::table('product_attribute_string')->truncate();
        DB::table('product_attribute_text')->truncate();
        DB::table('product_attribute_timestamp')->truncate();
        DB::table('attribute_options_child_products')->truncate();
        DB::table('inventory_items')->truncate();

        DB::table('catalog_inventories')->truncate();
        DB::table('catalog_inventory_items')->truncate();
        DB::table('product_images')->truncate();
        DB::table('product_builders')->truncate();

        DB::table('product_images')->truncate();
        // DB::table('product_tax_groups')->truncate();
        // DB::table('product_tax_group_tax_rule')->truncate();

        DB::table('carts')->truncate();
        DB::table('cart_items')->truncate();
        DB::table('klarna_orders')->truncate();
        DB::table('klarna_order_items')->truncate();
        DB::table('klarna_order_taxes')->truncate();
        DB::table('klarna_order_tax_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('order_items')->truncate();
        DB::table('order_addresses')->truncate();
        DB::table('order_comments')->truncate();
        DB::table('order_tax_items')->truncate();

        // DB::table('email_templates')->truncate();

        // Schema::dropIfExists('orders');
        // Schema::dropIfExists('order_items');
        // Schema::dropIfExists('order_addresses');
        // Schema::dropIfExists('order_taxes');
        // Schema::dropIfExists('order_tax_items');
        // Schema::dropIfExists('order_metas');
        // Schema::dropIfExists('order_transaction_logs');
        // Schema::dropIfExists('order_comments');
        // Schema::dropIfExists('order_status_states');
        // Schema::dropIfExists('order_statuses');

        Schema::enableForeignKeyConstraints();

        $this->info("Values truncated");

        //Artisan::call("migrate");
        // $this->info("Migrated");
        Artisan::call("db:seed", ["--class" => "Modules\Attribute\Database\Seeders\AttributeSetTableSeeder"]);
        Artisan::call("db:seed", ["--class" => "Modules\Attribute\Database\Seeders\AttributeTableSeeder"]);
        Artisan::call("db:seed", ["--class" => "Modules\Attribute\Database\Seeders\AttributeGroupTableSeeder"]);
        Artisan::call("db:seed", ["--class" => "Modules\Attribute\Database\Seeders\AttributeOptionTableSeeder"]);

        Artisan::call("db:seed", ["--class" => "Modules\Product\Database\Seeders\ProductTableSeeder"]);
        Artisan::call("db:seed", ["--class" => "Modules\Inventory\Database\Seeders\CatalogInventoryTableSeeder"]);
        // Artisan::call("db:seed", ["--class" => "Modules\Sales\Database\Seeders\SalesDatabaseSeeder"]);

        // Artisan::call("db:seed", ["--class" => "Modules\EmailTemplate\Database\Seeders\EmailTemplateSeeder"]);
        // Artisan::call("db:seed", ["--class" => "Modules\Sales\Database\Seeders\OrderCommentTableSeeder"]);
        // Artisan::call("db:seed", ["--class" => "Modules\Sales\Database\Seeders\OrderItemTableSeeder"]);
        // Artisan::call("db:seed", ["--class" => "Modules\Sales\Database\Seeders\OrderStatusStateTableSeeder"]);
        // Artisan::call("db:seed", ["--class" => "Modules\Sales\Database\Seeders\OrderStatusTableSeeder"]);
        // Artisan::call("db:seed", ["--class" => "Modules\Sales\Database\Seeders\OrderTableSeeder"]);
        // Artisan::call("db:seed", ["--class" => "Modules\Sales\Database\Seeders\OrderTaxItemTableSeeder"]);
        // Artisan::call("db:seed", ["--class" => "Modules\Sales\Database\Seeders\OrderTaxTableSeeder"]);
        // Artisan::call("db:seed", ["--class" => "Modules\Core\Database\Seeders\CacheTableSeeder"]);

        $this->info("Seeding completed");
        return true;
    }

}
