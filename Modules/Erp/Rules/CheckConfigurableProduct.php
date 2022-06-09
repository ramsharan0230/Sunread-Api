<?php

namespace Modules\Erp\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Modules\Product\Entities\Product;
use Modules\Product\Repositories\StoreFront\ProductRepository;

class CheckConfigurableProduct implements Rule
{
    protected $repository;

    public function __construct()
    {
        $this->repository = new ProductRepository();
    }

    public function passes($attribute, $value): bool
    {
        try
        {
            $product = $this->repository->queryFetch(["id" => $value]);
            $condition = true;
            if ($product?->type !== Product::CONFIGURABLE_PRODUCT) {
                $condition = false;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $condition;
    }

    public function message(): string
    {
        return "Invalid product id.";
    }
}
