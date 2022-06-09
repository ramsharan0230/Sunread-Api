<?php

namespace Modules\Product\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeType extends Model
{
    use HasFactory;

    protected $fillable = [ "value" ];

    public function product_attribute()
    {
        return $this->morphOne(ProductAttribute::class, "value");
    }
}
