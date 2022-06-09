<?php

namespace Modules\Category\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Modules\Category\Entities\CategoryValue;

class CategoryMigrate extends Command
{

    protected $signature = 'category:migrate';

    protected $description = 'Truncate existing data from category_values table and migrate and seed new data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        CategoryValue::query()->truncate();
        $this->info("Category values truncated");
        Artisan::call("migrate");
        $this->info("Category values migrated");
        Artisan::call("db:seed", ["--class" => "Modules\Category\Database\Seeders\CategoryValueTableSeeder"]);
        $this->info("Seeding completed");
    }
}
