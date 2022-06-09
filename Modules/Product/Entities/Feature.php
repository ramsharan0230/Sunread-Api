<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Traits\HasFactory;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [ "name", "image", "description", "status" ];
    protected $appends = [ "image_url" ];

    public function translations(): HasMany
    {
        return $this->hasMany(FeatureTranslation::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }
}
