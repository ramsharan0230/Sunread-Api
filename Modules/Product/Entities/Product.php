<?php

namespace Modules\Product\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Attribute\Entities\AttributeGroup;
use Modules\Attribute\Entities\AttributeSet;
use Modules\Brand\Entities\Brand;
use Modules\Category\Entities\Category;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Website;
use Modules\Inventory\Entities\CatalogInventory;
use Modules\Product\Traits\ElasticSearch\ElasticSearchFormat;
use Modules\Product\Traits\HasAttributeScope;

class Product extends Model
{
    use HasFactory, ElasticSearchFormat, HasAttributeScope;

    protected $fillable = [ "parent_id", "website_id", "brand_id", "attribute_set_id", "sku", "type", "status" ];

    const SIMPLE_PRODUCT = "simple";
    const CONFIGURABLE_PRODUCT = "configurable";

    public static $product_types = [
        self::SIMPLE_PRODUCT,
        self::CONFIGURABLE_PRODUCT,
    ];

    public static $SEARCHABLE = [ "sku", "type" ];



    public function __construct(?array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, "parent_id");
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class);
    }

    public function attribute_group(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class)->with(["attributes"]);
    }

    public function attribute_set(): BelongsTo
    {
        return $this->belongsTo(AttributeSet::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function product_attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class)->with(["value"]);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy("main_image", "desc")->orderBy("position");
    }

    public function variants(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    public function catalog_inventories(): HasMany
    {
        return $this->hasMany(CatalogInventory::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function attribute_configurable_products(): HasMany
    {
        return $this->hasMany(AttributeConfigurableProduct::class);
    }

    public function attribute_options_child_products(): HasMany
    {
        return $this->hasMany(AttributeOptionsChildProduct::class);
    }

    /**
     * Get the product builder values that owns the product.
     */
    public function productBuilderValues(): HasMany
    {
        return $this->hasMany(ProductBuilder::class);
    }
}
