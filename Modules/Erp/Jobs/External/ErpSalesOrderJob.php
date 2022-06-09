<?php

namespace Modules\Erp\Jobs\External;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Repositories\ErpSalesOrderRepository;

class ErpSalesOrderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    
    public $tries = 1;
    protected $repository;
    protected object $order;

    public function __construct(object $order)
    {
        $this->order = $order;
        $this->repository = new ErpSalesOrderRepository();
    }

    public function handle(): void
    {
        try
        {
            $this->repository->post($this->order);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
