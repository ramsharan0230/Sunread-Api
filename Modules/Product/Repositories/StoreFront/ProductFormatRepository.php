<?php

namespace Modules\Product\Repositories\StoreFront;

use Exception;
use Modules\Core\Facades\PriceFormat;
use Modules\Core\Repositories\BaseRepository;
use Modules\Inventory\Entities\CatalogInventory;
use Modules\Product\Entities\Product;
use Modules\Tax\Facades\NewTaxPrice;
use Modules\Tax\Facades\NewTaxPrices;

class ProductFormatRepository extends BaseRepository
{

    public function __construct()
    {
        $this->model = new Product();
    }

    //Product List Function

    public function getProductListInFormat(array $fetched, array $tax_data, array $price_data): array
    {
        try
        {
            $today = date('Y-m-d');
            $currentDate = date('Y-m-d H:m:s', strtotime($today));

            $fetched = $this->getPriceWithFormatAndTaxForList($fetched, $tax_data, $price_data);
            $fetched = $this->getSpecialPriceWithFormatForList($fetched, $currentDate, $price_data);
            $fetched = $this->getNewProductStatus($fetched, $currentDate);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getPriceWithFormatAndTaxForList(array $fetched, array $tax_data, array $price_data): array
    {
        try
        {
            if (isset($fetched["price"])) {
                $tax_class_id = isset($fetched["tax_class"]) ? $fetched["tax_class"] : (isset($fetched["tax_class_id"]) ? $fetched["tax_class_id"] :
                null);
                $calculateTax = NewTaxPrice::calculateForList($fetched["price"], $tax_data, $tax_class_id);
                $fetched["tax_amount"] = $calculateTax?->tax_rate_value;
                $fetched["price"] = $calculateTax->price + $fetched["tax_amount"];
            } else {
                $fetched["tax_amount"] = 0;
                $fetched["price"] = 0;
            }

            $fetched["price_formatted"] = PriceFormat::getForList($fetched["price"], $price_data);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getSpecialPriceWithFormatForList(array $fetched, string $currentDate, array $price_data): array
    {
        try
        {
            if (isset($fetched["special_price"])) {
                if (isset($fetched["special_from_date"])) {
                    $fromDate = date('Y-m-d H:m:s', strtotime($fetched["special_from_date"]));
                }
                if (isset($fetched["special_to_date"])) {
                    $toDate = date('Y-m-d H:m:s', strtotime($fetched["special_to_date"])); 
                }
                if (!isset($fromDate) && !isset($toDate)) {
                    $fetched["special_price_formatted"] = PriceFormat::getForList($fetched["special_price"], $price_data);
                } else {
                    $fetched["special_price_formatted"] = (($currentDate >= $fromDate) && ($currentDate <= $toDate)) ? PriceFormat::getForList($fetched["special_price"], $price_data) : null;
                }
            } else {
                $fetched["special_price"] = null;
                $fetched["special_price_formatted"] = null;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    //Product Detail Function

    public function getProductInFormat(array $fetched, object $request, object $product): array
    {
        try
        {
            $today = date('Y-m-d');
            $currentDate = date('Y-m-d H:m:s', strtotime($today));

            $tax_class_id = isset($fetched["tax_class"]) 
                ? $fetched["tax_class"]
                : ((isset($fetched["tax_class_id"]) && !is_array($fetched["tax_class_id"])) 
                    ? $fetched["tax_class_id"]
                    : null
                );

            $fetched["price"] = NewTaxPrices::calculate($request, $product->id);
            unset($fetched["price"]["final"]["rules"]);
            $fetched = $this->getNewProductStatus($fetched, $currentDate);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getPriceWithFormatAndTax(array $fetched, object $request, object $store): array
    {
        try
        {
            if (isset($fetched["price"])) {

                $tax_class_id = isset($fetched["tax_class"]) 
                    ? $fetched["tax_class"]
                    : ((isset($fetched["tax_class_id"]) && !is_array($fetched["tax_class_id"])) 
                        ? $fetched["tax_class_id"]
                        : null
                    );

                $calculateTax = NewTaxPrice::calculate($request, $fetched["price"], $tax_class_id);
                $fetched["tax_amount"] = $calculateTax?->tax_rate_value;
                $fetched["price"] = $calculateTax->price + $fetched["tax_amount"];
            } else {
                $fetched["tax_amount"] = 0;
                $fetched["price"] = 0;
            }
            $fetched["price_formatted"] = PriceFormat::get($fetched["price"], $store->id, "store");
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getSpecialPriceWithFormat(array $fetched, string $currentDate, object $store): array
    {
        try
        {
            if (isset($fetched["special_price"])) {
                if (isset($fetched["special_from_date"])) {
                    $fromDate = date('Y-m-d H:m:s', strtotime($fetched["special_from_date"]));
                }
                if (isset($fetched["special_to_date"])) {
                    $toDate = date('Y-m-d H:m:s', strtotime($fetched["special_to_date"])); 
                }
                if (!isset($fromDate) && !isset($toDate)) {
                    $fetched["special_price_formatted"] = PriceFormat::get($fetched["special_price"], $store->id, "store");
                } else {
                    $fetched["special_price_formatted"] = (($currentDate >= $fromDate) && ($currentDate <= $toDate)) ? PriceFormat::get($fetched["special_price"], $store->id, "store") : null;
                }
            } else {
                $fetched["special_price"] = null;
                $fetched["special_price_formatted"] = null;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getNewProductStatus(array $fetched, string $currentDate): array
    {
        try
        {         
            if (isset($fetched["new_from_date"]) && isset($fetched["new_to_date"])) { 
                if (isset($fetched["new_from_date"])) {
                    $fromNewDate = date('Y-m-d H:m:s', strtotime($fetched["new_from_date"]));
                }
                if (isset($fetched["new_to_date"])) {
                    $toNewDate = date('Y-m-d H:m:s', strtotime($fetched["new_to_date"])); 
                }
                if (isset($fromNewDate) && isset($toNewDate)) {
                    $fetched["is_new_product"] = (($currentDate >= $fromNewDate) && ($currentDate <= $toNewDate)) ? 1 : 0;
                }
                unset($fetched["new_from_date"], $fetched["new_to_date"]);
            } else {
                $fetched["is_new_product"] = 0;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function changeProductStockStatus(array $fetched): array
    {
        try
        {
            $product = Product::with([ "parent", "variants" ])->whereId($fetched["id"])->first();

            if ($product) {
                if ($product->type == "simple" && $product->parent_id) {
                    $variant_ids = $product->parent->variants->pluck("id")->toArray();
                }
                if ($product->type == "configurable") {
                    $variant_ids = $product->variants->pluck("id")->toArray();
                }
    
                if (isset($variant_ids)) {
                    $variant_stock = CatalogInventory::whereIn("product_id", $variant_ids)->whereIsInStock(1)->where("quantity", ">", 0)->first();
                    $fetched["is_in_stock"] = $variant_stock ? 1 : 0;
                    $fetched["stock_status_value"] = $variant_stock ? "In stock" : "Out of stock";
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }
}
