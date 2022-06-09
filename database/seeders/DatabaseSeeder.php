<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(\Modules\Core\Database\Seeders\CoreDatabaseSeeder::class);
        $this->call(\Modules\User\Database\Seeders\UserDatabaseSeeder::class);
        $this->call(\Modules\Customer\Database\Seeders\CustomerDatabaseSeeder::class);
        $this->call(\Modules\Category\Database\Seeders\CategoryDatabaseSeeder::class);
        $this->call(\Modules\ClubHouse\Database\Seeders\ClubHouseDatabaseSeeder::class);
        $this->call(\Modules\Attribute\Database\Seeders\AttributeDatabaseSeeder::class);
        $this->call(\Modules\Brand\Database\Seeders\BrandDatabaseSeeder::class);
        $this->call(\Modules\Product\Database\Seeders\ProductDatabaseSeeder::class);
        $this->call(\Modules\Review\Database\Seeders\ReviewDatabaseSeeder::class);
        $this->call(\Modules\UrlRewrite\Database\Seeders\UrlRewriteDatabaseSeeder::class);
        $this->call(\Modules\Coupon\Database\Seeders\CouponDatabaseSeeder::class);
        $this->call(\Modules\Page\Database\Seeders\PageDatabaseSeeder::class);
        $this->call(\Modules\Country\Database\Seeders\CountryDatabaseSeeder::class);
        $this->call(\Modules\Tax\Database\Seeders\TaxDatabaseSeeder::class);
        $this->call(\Modules\Inventory\Database\Seeders\InventoryDatabaseSeeder::class);
        $this->call(\Modules\Erp\Database\Seeders\ErpDatabaseSeeder::class);
        $this->call(\Modules\EmailTemplate\Database\Seeders\EmailTemplateDatabaseSeeder::class);
        $this->call(\Modules\Cart\Database\Seeders\CartsTableSeeder::class);
        $this->call(\Modules\Sales\Database\Seeders\SalesDatabaseSeeder::class);
        $this->call(\Modules\NavigationMenu\Database\Seeders\NavigationMenuDatabaseSeeder::class);
        $this->call(\Modules\SizeChart\Database\Seeders\SizeChartDatabaseSeeder::class);
    }
}
