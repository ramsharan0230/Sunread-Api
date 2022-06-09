<?php

namespace Modules\Erp\Jobs\Mapper;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Traits\HasErpValueMapper;

class ErpMigrateVisibilityUpdateJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasErpValueMapper;

    protected $product;

    public $tries = 10;
    public $timeout = 90000;

    public function __construct(object $product)
    {
        $this->product = $product;
    }

    public function handle(): void
    {
        try
        {
            $this->updateVisibility($this->product);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
