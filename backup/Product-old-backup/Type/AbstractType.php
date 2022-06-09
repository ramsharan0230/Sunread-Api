<?php

namespace Modules\Product\Type;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Core\Traits\FileManager;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductAttributeValue;
use Modules\Product\Repositories\ProductAttributeValueRepository;
use Modules\Product\Services\ProductImageRepository;

/**
 * Abstract class Type
 *
 * @author    Hemant Achhami
 * @copyright 2020 Hazesoft co
 */
abstract class AbstractType
{
    use FileManager;

    /**
     * Has child products aka variants
     *
     * @var boolean
     */

    protected $isComposite = false;

    protected $hasVariants = false;


    protected $product, $folder_path,$productImage,$attributeValueRepository,$productItem;
    private $folder = 'product';

    /**
     * AbstractType constructor.
     * @param Product $product
     * @param ProductImageRepository $productImage
     * @param ProductAttributeValueRepository $attributeValueRepository
     */
    public function __construct(Product $product, ProductImageRepository $productImage ,ProductAttributeValueRepository $attributeValueRepository)
    {
        $this->product = $product;
        $this->folder_path = storage_path('app/public/images/') . $this->folder . DIRECTORY_SEPARATOR;
        $this->productImage =  $productImage;
        $this->attributeValueRepository = $attributeValueRepository;
    }

    /**
     * create a simple product
     * @param array $data
     * @return Product
     */
    public function create(array $data):Product
    {
        return $this->product->create($data);
    }


    /**
     * Update products table
     *
     * @param array $data
     * @param $id
     * @return Product
     * @throws \Exception
     */
    public function update(array $data, $id):Product
    {
        $product = $this->product->findOrFail($id);

        //updating product-table
        $product->update($data);

        //we fetch only the associated attributes of particular product family
        $associated_attributes = $product->associatedAttributes();

        //update the associated attributed
        $this->updateAttributes($associated_attributes ,$product,$data);

        //updating product-categories data
        if (isset($data['categories'])) {
            $product->categories()->sync($data['categories']);
        }

        //Upload ProductImage
        $this->productImage->uploadProductImages($product);

        //TODO::future => Update cross-sell, up-sells, inventories

        return $product;
    }

    /**
     * Fetch the attribute value from request dynamically
     * @param array $data
     * @param $attribute
     * @return int|mixed|null|string
     */
    private function fetchAttributeValue(array $data, $attribute)
    {

        if ($attribute->type == 'boolean') {
            return $data[$attribute->slug] = isset($data[$attribute->slug]) && $data[$attribute->slug] ? 1 : 0;
        }


        if ($attribute->type == 'price' && isset($data[$attribute->slug]) && $data[$attribute->slug] == '') {
            return $data[$attribute->slug] = null;
        }

        if ($attribute->type == 'date' && $data[$attribute->slug] == '') {
            return $data[$attribute->slug] = null;
        }

        if ($attribute->type == 'multiselect' || $attribute->type == 'checkbox') {
            return $data[$attribute->slug] = implode(",", $data[$attribute->slug]);
        }


        if ($attribute->type == 'image' || $attribute->type == 'file') {
            return $data[$attribute->slug] = gettype($data[$attribute->slug]) == 'object'
                ? $this->uploadFile(request()->file($attribute->slug), $this->folder_path)
                : NULL;
        }
        return $data[$attribute->slug];

    }

    /**
     * Specify type instance product
     *
     * @param  Product $product
     * @return AbstractType
     */
    public function setProduct($product)
    {

        $this->product = $product;

        return $this;
    }


    /**
     * Return true if this product type is saleable
     *
     * @return boolean
     */
    public function isSaleable()
    {
        if (!$this->product->status) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve product attributes
     *
     * @return Collection
     */
    public function getEditableAttributes()
    {
        return $this->product->attribute_family->custom_attributes()->get();
    }

    /**
     * Returns validation rules
     *
     * @return array
     */
    public function getTypeValidationRules()
    {
        return [];
    }

    /**
     * @param $associated_attributes
     * @param $product
     * @param $data
     */
    private function updateAttributes($associated_attributes,$product ,$data)
    {
        //Updating each product-attribute table
        foreach ($associated_attributes as $attribute) {

            //ignore the attribute with missing slug
            if (!isset($data[$attribute->slug])) {
                continue;
            }

            //fetch the attribute value from request data
            $attribute_value = $this->fetchAttributeValue($data, $attribute);

            //create or update attribute value
            $productAttribute = ProductAttributeValue::firstOrNew([
                'product_id' => $product->id,
                'attribute_id' => $attribute->id,
            ]);
            $productAttribute->fill(
                [
                    'product_id' => $product->id,
                    'attribute_id' => $attribute->id,
                    ProductAttributeValue::$attributeTypeFields[$attribute->type] => $attribute_value
                ]
            );
            $productAttribute->save();
        }
    }


    /**
     * Return true if this product can have variants
     *
     * @return bool
     */
    public function hasVariants()
    {
        return $this->hasVariants;
    }

    /**
     * Return true if this product can be composite
     *
     * @return bool
     */
    public function isComposite()
    {
        return $this->isComposite;
    }

    /**
     * Get product minimal price
     *
     * @param int $qty
     *
     * @return float
     */
    public function getFinalPrice($qty = null)
    {
        return $this->getMinimalPrice($qty);
    }

    /**
     * Returns the product's minimal price
     *
     * @param int $qty
     *
     * @return float
     */
    public function getSpecialPrice($qty = null)
    {
        return $this->haveSpecialPrice($qty) ? $this->product->special_price : $this->product->price;
    }

    /**
     * @param int $qty
     *
     * @return bool
     */
    public function haveSpecialPrice($qty = null)
    {

        //customer group price
        //$customerGroupPrice = $this->getCustomerGroupPrice($this->product, $qty);

        $haveSpecialPrice = false;
        if ((is_null($this->product->special_price) || ! (float)$this->product->special_price)) {
            return false;
        }

        if($this->product->price >= $this->product->special_price){
            return false;
        }

        if(isset($this->product->special_price_from) && isset($this->product->special_price_to)){
            if(Carbon::now()->between($this->product->special_price_from,$this->product->special_price_to)){
                return true;
            }
        }

        return false;
    }

    public function getMinimalPrice($qty = null)
    {

        if ($this->haveSpecialPrice($qty)) {
            return $this->product->special_price;
        }

        return $this->product->price;
    }


}
