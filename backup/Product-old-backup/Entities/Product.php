<?php

namespace Modules\Product\Entities;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeSet;
use Modules\Category\Entities\Category;

class Product extends Model
{

    protected $fillable = ['type', 'attribute_family_id', 'sku', 'parent_id', 'slug'];
    protected $typeInstance;
    public static $SEARCHABLE = ['type', 'attribute_family_id', 'sku', 'parent_id', 'slug' ,'name'];

    /**
     * Retrieve product attributes
     *
     *
     * @param bool  $skipSuperAttribute
     * @return Collection
     */
    public function getEditableAttributes($group = null, $skipSuperAttribute = true)
    {
        return $this->getTypeInstance()->getEditableAttributes($group, $skipSuperAttribute);
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
        return $this->belongsTo(static::class, 'parent_id');
    }

    /**
     * The categories that belong to the product.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    /**
     * Get the product attribute values that owns the product.
     */
    public function attribute_values()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    /**
     *
     * Retrieve type instance
     * @param $type
     * @return AbstractType
     */
    public function getTypeInstance($type = null)
    {
        if ($this->typeInstance)
            return $this->typeInstance;

        if($type){
            $this->type = $type;
        }
        $this->typeInstance = app(config('product_types.' . $this->type . '.class'));

        $this->typeInstance->setProduct($this);

        return $this->typeInstance;
    }


    /**
     * Get the product attribute family that owns the product.
     */
    public function attribute_set()
    {
        return $this->belongsTo(AttributeSet::class);
    }


    public function associatedAttributes()
    {
        return $this->attribute_family->custom_attributes()->get();
    }

    /**
     * The images that belong to the product.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * The super attributes that belong to the product.
     */
    public function super_attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_super_attributes');
    }

    /**
     * Get the product flat entries that are associated with product.
     * May be one for each locale and each channel.
     */
    public function product_flats()
    {
        return $this->hasMany(ProductFlat::class, 'product_id');
    }

}
