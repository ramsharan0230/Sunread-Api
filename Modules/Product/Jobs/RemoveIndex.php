<?php

namespace Modules\Product\Jobs;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Product\Traits\ElasticSearch\HasIndexing;

class RemoveIndex implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasIndexing;

    public $store;

    public function __construct(object $store)
    {
        $this->store = $store;
    }

    public function handle(): void
    {
        try
        {
            $this->removeIndex($this->store);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
