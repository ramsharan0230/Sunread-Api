<?php

namespace Modules\Product\Jobs;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;
use Modules\Core\Entities\Store;
use Modules\Product\Traits\ElasticSearch\HasIndexing;

class DeleteIndices implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasIndexing;

    public function __construct()
    {
    }

    public function handle(): void
    {
        try
        {
            $stores = Store::get();
            $batch = Bus::batch([])->onQueue("index")->dispatch();
            foreach($stores as $store) $batch->add(new RemoveIndex($store));
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
