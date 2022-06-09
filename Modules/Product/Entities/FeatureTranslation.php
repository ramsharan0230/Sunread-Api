<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasFactory;

class FeatureTranslation extends Model
{
    use HasFactory;

    protected $fillable = [ "store_id", "feature_id", "name", "description" ];
    public $timestamps = false;
}
