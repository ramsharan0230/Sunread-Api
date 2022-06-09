<?php

namespace Modules\Attribute\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\HasFactory;
use Modules\Product\Entities\AttributeOptionsChildProduct;

class AttributeOption extends Model
{
    use HasFactory;

    public static $SEARCHABLE = [ "name" ];
    protected $fillable = [ "attribute_id", "name", "position", "is_default", "code" ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AttributeOptionTranslation::class);
    }

    public function attribute_options_child_products(): HasMany
    {
        return $this->hasMany(AttributeOptionsChildProduct::class);
    }
}
