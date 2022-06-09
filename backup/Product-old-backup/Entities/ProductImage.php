<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    public $timestamps = false;
    protected $fillable = ['product_id','path','thumbnail','small_image', 'main_image'];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

}
