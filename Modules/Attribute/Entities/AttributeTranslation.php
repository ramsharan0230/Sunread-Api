<?php

namespace Modules\Attribute\Entities;

use Illuminate\Database\Eloquent\Model;

class AttributeTranslation extends Model
{
    protected $fillable = [ "store_id", "attribute_id", "name" ];
    public $timestamps = false;
}
