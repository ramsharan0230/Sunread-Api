<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ImageType extends Model
{
    protected $fillable = [ "image_type_id", "product_image_id" ];

    public function product_images(): BelongsToMany
    {
        return $this->belongsToMany(ProductImage::class);
    }
}
