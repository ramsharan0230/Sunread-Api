<?php


namespace Modules\Attribute\Observers;

use Illuminate\Support\Facades\Artisan;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Product\Jobs\ReindexMigrator;

class AttributeOptionObserver
{
    public function created(AttributeOption $attribute_option)
    {

    }

    public function updated(AttributeOption $attribute_option)
    {
        // Artisan::call("queue:clear", ["--queue" => "index"]);
        // ReindexMigrator::dispatch()->onQueue("index");
    }

    public function deleted(AttributeOption $attribute_option)
    {
        // Artisan::call("queue:clear", ["--queue" => "index"]);
        // ReindexMigrator::dispatch()->onQueue("index");
    }
}
