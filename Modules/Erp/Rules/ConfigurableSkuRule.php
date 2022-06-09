<?php

namespace Modules\Erp\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\Product\Entities\Product;
use Modules\Product\Repositories\StoreFront\ProductRepository;

class ConfigurableSkuRule implements Rule
{
    protected $productRepository;
    protected $invalidSkus;

    public function __construct()
    {
       $this->productRepository = new ProductRepository();
    }

    public function passes($attribute, $value): bool
    {
        $skus = $this->productRepository->query(function ($query) use ($value) {
            return $query->select("sku", "type")
                ->whereIn("sku", $value)
                ->whereType(Product::CONFIGURABLE_PRODUCT)
                ->get()
                ->pluck("sku")
                ->toArray();
            });
        $invalidSku = array_filter($value, function ($sku) use ($skus) {
            return !in_array($sku, $skus);
        });
        if ($invalidSku !== []) {
            $this->invalidSkus = implode(", ", $invalidSku);
            return false;
        } else {
            return true;
        }
    }

    public function message(): string
    {
        return "The given sku {$this->invalidSkus} is invalid.";
    }
}
