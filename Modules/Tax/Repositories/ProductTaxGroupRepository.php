<?php

namespace Modules\Tax\Repositories;

use Modules\Attribute\Entities\Attribute;
use Modules\Product\Entities\ProductAttribute;
use Modules\Tax\Entities\ProductTaxGroup;
use Modules\Core\Repositories\BaseRepository;
use Exception;

class ProductTaxGroupRepository extends BaseRepository
{
    public function __construct(ProductTaxGroup $productTaxGroup)
    {
        $this->model = $productTaxGroup;
        $this->model_key = "product-tax-groups";

        $this->rules = [
            "name" => "required",
            "description" => "required"
        ];
    }

    public function checkTaxOnProduct(int $id): ?int
    {
        try
        {
            $product_attr = ProductAttribute::query()->with(["value"]);
            $attribute_id = Attribute::whereSlug("tax_class_id")->first()?->id;
            $product_attr_count = $product_attr->whereAttributeId($attribute_id)->get()->filter( fn ($attr_product) => $attr_product->value->value == $id)->count();
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return $product_attr_count;
    }
}
