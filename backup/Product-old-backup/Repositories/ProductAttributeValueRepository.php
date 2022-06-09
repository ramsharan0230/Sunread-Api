<?php


namespace Modules\Product\Repositories;

use Modules\Attribute\Entities\Attribute;
use Modules\Product\Entities\ProductAttributeValue;

class ProductAttributeValueRepository
{


    /**
     * @param array $data
     * @return mixed
     */
    public function createProductAttribute(array $data)
    {

        if (isset($data['attribute_id'])) {
            $attribute = Attribute::find($data['attribute_id']);
        } else {
            $attribute = Attribute::where('slug', $data['slug'])->first();
        }

        if (! $attribute)
            return;

        $data[ProductAttributeValue::$attributeTypeFields[$attribute->type]] = $data['value'];

        return ProductAttributeValue::create($data);
    }
}

