<?php

namespace Modules\ClubHouse\Entities;

use Modules\Core\Entities\Website;
use Modules\Core\Traits\Sluggable;
use Modules\Core\Traits\HasFactory;
use Modules\ClubHouse\Traits\HasScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ClubHouse.
 *
 * @package Modules\ClubHouse\Entities
 *
 * @property integer id
 * @property integer position
 * @property integer website_id
 * @property string type
 * @property integer status
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class ClubHouse extends Model
{
    use HasFactory, Sluggable, HasScope;

    /**
     * Arrays that are Mass Assignable
     */
    protected $fillable = ["position", "website_id", "status"];

    // Append data with Values [ClubHouseValue]
    protected $with = [ "values" ];

    // Searchable
    public static $SEARCHABLE = [];

    protected $casts = [];

    /**
     * Get Image URL
     */
    public function image_url(): ?string
    {
        if (!$this->image) return null;
        return Storage::url($this->image);
    }

    /**
     * Get Image URL Attribute
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_url();
    }

    /**
     * Many to One Relation Between ClubHouse and Website
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * One to Many Relation Between ClubHouse and ClubHouseValue
     */
    public function values(): HasMany
    {
        return $this->hasMany(ClubHouseValue::class);
    }
}
