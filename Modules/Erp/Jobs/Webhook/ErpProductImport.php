<?php

namespace Modules\Erp\Jobs\Webhook;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Event;
use Modules\Erp\Repositories\ErpProductRepository;

class ErpProductImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $data;
    protected $repository;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->repository = new ErpProductRepository();
    }

    public function handle(): void
    {
        try
        {
            switch ($this->data["type"]) {
                case "create":
                    $this->repository->createProduct($this->data);
                break;

                case "update":
                    ErpProductUpdate::dispatch($this->data)->onQueue("erp");
                break;

                case "inventory":
                    ErpProductInventoryUpdate::dispatch($this->data)->onQueue("erp");
                break;

                case "price":
                    ErpProductPriceUpdate::dispatch($this->data)->onQueue("erp");
                break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
