<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\ProductImage;

class ProductFlat extends Model
{
    protected $table = 'product_flat';
    public static $SEARCHABLE = ['name', 'slug' ,'sku'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public $timestamps = false;

    /**
     * Retrieve type instance
     *
     * @return AbstractType
     */
    public function getTypeInstance()
    {
        return $this->product->getTypeInstance();
    }

    /**
     * Get the product attribute family that owns the product.
     */
    public function getAttributeSetAttribute()
    {
        return $this->product->attribute_set;
    }

    /**
     * Get the product that owns the attribute value.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variants that owns the product.
     */
    public function variants()
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    /**
     * Get the product that owns the product.
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get product type value from base product
     */
    public function getTypeAttribute()
    {
        return $this->product->type;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isSaleable()
    {
        return $this->product->isSaleable();
    }

    /**
     * @return integer
     */
    public function totalQuantity()
    {
        return $this->product->totalQuantity();
    }

    /**
     * @param integer $qty
     *
     * @return bool
     */
    public function haveSufficientQuantity($qty)
    {
        return $this->product->haveSufficientQuantity($qty);
    }

    /**
     * @return bool
     */
    public function isStockable()
    {
        return $this->product->isStockable();
    }

    /**
     * The images that belong to the product.
     */
    public function images()
    {
        return (ProductImage::class)
            ::where('product_images.product_id', $this->product_id)
            ->select('product_images.*');
    }

    /**
     * Get all of the attributes for the attribute groups.
     */
    public function getImagesAttribute()
    {
        return $this->images()->get();
    }



    /**
     * Get all of the reviews for the attribute groups.
     */
    public function getReviewsAttribute()
    {
        return $this->reviews()->get();
    }

    /**
     * The related products that belong to the product.
     */
    public function related_products()
    {
        return $this->product->related_products();
    }


}
