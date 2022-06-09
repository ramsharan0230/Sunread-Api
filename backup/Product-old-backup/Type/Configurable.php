<?php

namespace Modules\Product\Type;

use Modules\Attribute\Entities\Attribute;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductAttributeValue;
use Illuminate\Support\Str;


/**
 * Class Configurable.
 *
 * @author    Hemant Achhami
 * @copyright 2020 Hazesoft co
 */

class Configurable extends AbstractType
{
    protected $hasVariants = true;
    protected $isComposite =  true;


    /**
     * Create configurable product
     * Configurable product = parent product + variant products
     * @param array $data
     * @return Product
     */
    public function create(array $data):Product
    {
        //create parent configurable product item
        $product = $this->product->create($data);

        //If the request has super-attributes(attributes with options ie color,size);
        //Then create variants
        if (isset($data['super_attributes'])) {

            $super_attributes = [];

            //create product-superattribute
            foreach ($data['super_attributes'] as $attributeCode => $attributeOptions) {
                $attribute = Attribute::where('slug', $attributeCode)->first();
                $super_attributes[$attribute->id] = $attributeOptions;
                $product->super_attributes()->attach($attribute->id);
            }

            //generate multiple product(variant) combination on the basis of color and size for variants
            //create variants
            foreach (array_permutation($super_attributes) as $permutation) {
                $variants = isset($data['variants'])? $data['variants']:[];
                $this->createVariant($product, $permutation,$variants);
            }

        }

        return $product;
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return Product
     * @throws \Exception
     */
    public function update(array $data, $id):Product
    {

        //Update main parent product like simple product
        $product = parent::update($data, $id);


        //get previous keys
        $previousVariantIds = $product->variants->pluck('id');

        if (isset($data['variants'])) {
            foreach ($data['variants'] as $variantId => $variantData) {

                if (Str::contains($variantId, 'variant_')) {
                    $permutation = [];

                    foreach ($product->super_attributes as $superAttribute) {
                        $permutation[$superAttribute->id] = $variantData[$superAttribute->slug];
                    }
                    $this->createVariant($product, $permutation, $variantData);
                } else {
                    if (is_numeric($index = $previousVariantIds->search($variantId)))
                        $previousVariantIds->forget($index);
                    $this->updateVariant($variantData, $variantId);
                }
            }
        }

        foreach ($previousVariantIds as $variantId) {
            $variant = Product::find($variantId);
            $variant->delete();
        }
        return $product;
    }

    /**
     * create a variant is analogue to create a simple product
     * @param mixed $product
     * @param array $permutation
     * @param array $data
     * @return mixed
     */
    public function createVariant($product, $permutation, $data = []):Product
    {
        //default data for store
        if (!count($data)) {
            $data = [
                "sku" => $product->sku . '-variant-' . implode('-', $permutation),
                "slug" => $product->slug . '-variant-' . implode('-', $permutation),
                "name" => "",
                "inventories" => [],
                "price" => 0,
                "weight" => 0,
                "status" => 1
            ];
        }

        $typeOfVariants = 'simple';

        //store variant
        $variant = Product::create([
            'parent_id' => $product->id,
            'type' => $typeOfVariants,
            'attribute_family_id' => $product->attribute_family_id,
            'sku' => $data['sku'],
            'slug' => $data['slug'],
        ]);

        //store attribute values
        foreach (['sku', 'name', 'price', 'weight', 'status'] as $attributeCode) {
            $attribute = Attribute::where('slug', $attributeCode)->first();
            $this->attributeValueRepository->createProductAttribute(
                [
                    'product_id' => $variant->id,
                    'attribute_id' => $attribute->id,
                    'value' => $data[$attributeCode],
                    'slug' => $attributeCode
                ]
            );

        }

        //store permuted attributes(usually color and size)
        foreach ($permutation as $attributeId => $optionId) {
            $this->attributeValueRepository->createProductAttribute(
                [
                    'product_id' => $variant->id,
                    'attribute_id' => $attributeId,
                    'value' => $optionId
                ]);
        }

        return $variant;
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function updateVariant(array $data, $id)
    {
        $variant = $this->product->findOrFail($id);

        $variant->update(['sku' => $data['sku']]);

        foreach (['sku', 'name', 'price', 'weight', 'status'] as $attributeCode) {
            $attribute = Attribute::where('slug', $attributeCode)->first();

            $attributeValue = ProductAttributeValue::where([
                'product_id' => $id,
                'attribute_id' => $attribute->id,
            ])->first();

            if (!$attributeValue) {
                ProductAttributeValue::create([
                    'product_id' => $id,
                    'attribute_id' => $attribute->id,
                    'value' => $data[$attribute->slug],
                ]);
            } else {
                $attributeValue->update([
                    ProductAttributeValue::$attributeTypeFields[$attribute->type] => $data[$attribute->slug]
                ]);
            }
        }

        return $variant;
    }

    /**
     * Returns children ids
     *
     * @return array
     */
    public function getChildrenIds()
    {
        return $this->product->variants()->pluck('id')->toArray();
    }

    public function getTypeValidationRules()
    {
        return [

            'variants.*.name' => 'required',
            'variants.*.sku' => 'required',
            'variants.*.price' => 'required',
            'variants.*.weight' => 'required',
        ];
    }

}
