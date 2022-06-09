<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Traits\HasFactory;
use Modules\Product\Entities\Product;

class Channel extends Model
{
    use HasFactory;

    public static $SEARCHABLE = [ "code", "hostname", "name", "description" ];
    protected $fillable = [ "code", "hostname", "name", "description", "default_store_id", "website_id", "status" ];
    protected $with = [ "stores" ];

    public function default_store(): BelongsTo
    {
        return $this->belongsTo(Store::class, "default_store_id");
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, "website_id");
    }

    private function get_url(?string $path): ?string
    {
        return $path ? Storage::url($path) : null;
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function getDefaultStoreAttribute(): ?object
    {
        return $this->stores->first();
    }
}
