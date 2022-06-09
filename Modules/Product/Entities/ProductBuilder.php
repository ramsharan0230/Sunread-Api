<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasFactory;

class ProductBuilder extends Model
{
    use HasFactory;

    protected $fillable = [ "product_id", "attribute", "value", "scope", "scope_id", "position"];
    protected $casts = [ "value" => "array" ];
    
}
