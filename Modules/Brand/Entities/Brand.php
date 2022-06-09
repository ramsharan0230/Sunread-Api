<?php

namespace Modules\Brand\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasFactory;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Traits\Sluggable;

class Brand extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [ "slug", "name", "image", "description", "meta_title", "meta_description", "meta_keywords" ];
    
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image): null;
    }
}
