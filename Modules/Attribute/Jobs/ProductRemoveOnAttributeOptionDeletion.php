<?php

namespace Modules\Attribute\Jobs;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Event;
use Modules\Product\Entities\Product;
use Modules\Product\Repositories\ProductBaseRepository;

class ProductRemoveOnAttributeOptionDeletion implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable; 
    use SerializesModels;

    protected array $product_ids;
    protected $productbaserepository;

    public function __construct(array $product_ids)
    {
        $this->product_ids = $product_ids;
        $this->productbaserepository = new ProductBaseRepository();
    }

    public function handle(): void
    {
        try
        {
            $products = $this->productbaserepository->query(function ($query) {
                return $query->whereIn("id", $this->product_ids)->get();
            });
            $products->map(function ($product) {
                $product->delete();
                Event::dispatch("catalog.products.delete.after", $product);
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
