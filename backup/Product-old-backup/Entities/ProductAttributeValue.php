<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Attribute\Entities\Attribute;

class ProductAttributeValue extends Model
{
    public $timestamps = false;

    /**
     * @var array
     */
    public static $attributeTypeFields = [
        'text' => 'text_value',
        'textarea' => 'text_value',
        'price' => 'float_value',
        'boolean' => 'boolean_value',
        'select' => 'integer_value',
        'multiselect' => 'text_value',
        'datetime' => 'datetime_value',
        'date' => 'date_value',
        'file' => 'text_value',
        'image' => 'text_value',
        'checkbox' => 'text_value',
    ];

    protected $fillable = [
        'product_id',
        'attribute_id',
        'channel_id',
        'locale',
        'text_value',
        'boolean_value',
        'integer_value',
        'float_value',
        'datetime_value',
        'date_value',
        'json_value'
    ];

    /**
     * Get the attribute that owns the attribute value.
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get the product that owns the attribute value.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }



}
