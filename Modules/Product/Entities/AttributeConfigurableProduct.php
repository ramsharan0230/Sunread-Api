<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;

class AttributeConfigurableProduct extends Model
{
    protected $fillable = [ "product_id", "attribute_id", "used_in_grouping" ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    
    
}
