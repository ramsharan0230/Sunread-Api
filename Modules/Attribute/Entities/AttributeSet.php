<?php

namespace Modules\Attribute\Entities;

use Illuminate\Support\Facades\DB;
use Modules\Core\Traits\Sluggable;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\HasFactory;

class AttributeSet extends Model
{
    use Sluggable, HasFactory;

    public static $SEARCHABLE = [ "name" ];
    protected $fillable = [ "name" ];

    public function attribute_groups(): HasMany
    {
        return $this->hasMany(AttributeGroup::class, "attribute_set_id");
    }

    public function custom_attributes(): Builder
    {
        return DB::table("attributes")
            ->join("attribute_groups", "attributes.attribute_group_id", "=", "attribute_groups.id")
            ->join("attribute_sets", "attribute_groups.attribute_set_id", "=", "attribute_sets.id")
            ->where("attribute_sets.id", $this->id)
            ->select("attributes.*");
    }
}
