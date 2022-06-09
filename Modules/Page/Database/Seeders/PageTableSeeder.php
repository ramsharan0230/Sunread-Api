<?php

namespace Modules\Page\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Page\Entities\Page;
use Modules\Page\Entities\PageScope;

class PageTableSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::factory()->create();

        PageScope::create([
            "page_id" => $page->id,
            "scope" => "store",
            "scope_id" => 0
        ]);
    }
}
