<?php

namespace Modules\Attribute\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\HasFactory;
use Modules\Attribute\Traits\HasMapper;
use Modules\Core\Traits\HasTranslation;
use Modules\Core\Traits\Sluggable;
use Modules\Product\Entities\ProductAttribute;

class Attribute extends Model
{
    use Sluggable, HasFactory, HasTranslation, HasMapper;

    public static $SEARCHABLE = [ "name", "type" ];
    protected $fillable = [ "slug", "name", "type", "scope", "validation", "is_required", "is_unique", "is_searchable", "search_weight", "is_user_defined", "is_visible_on_storefront", "use_in_layered_navigation", "position", "comparable_on_storefront", "default_value", "is_synchronized" ];
    protected $appends = [ 'type_validation' ];
    protected $with = [ "attribute_options" ];
    
    public $translatedAttributes = ["name"];
    public $translatedModels = [ AttributeTranslation::class, "attribute_id" ];

    public function attribute_group(): BelongsToMany
    {
        return $this->belongsToMany(AttributeGroup::class, "attribute_group_attributes")->withPivot('position');
    }

    public function attribute_options(): HasMany
    {
        return $this->hasMany(AttributeOption::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AttributeTranslation::class);
    }

    public function generateValidation(): array
    {
        $validation = $this->is_required ? ["required"] : ["sometimes", "nullable"];
        return array_merge($validation, [config("validation.{$this->type}")]);
    }

    public function getTypeValidationAttribute(): ?string
    {
        return implode("|", $this->generateValidation());
    }

    public function product_attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class)->with(["value"]);
    }
}
