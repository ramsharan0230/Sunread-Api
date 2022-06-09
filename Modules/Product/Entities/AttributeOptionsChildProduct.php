<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;

class AttributeOptionsChildProduct extends Model
{
    protected $fillable = [ "product_id", "attribute_option_id" ];

    public function attribute_option(): BelongsTo
    {
        return $this->belongsTo(AttributeOption::class);
    }
    
    public function variant_product(): BelongsTo
    {
        return $this->belongsTo(Product::class, "product_id");
    }
    
}
