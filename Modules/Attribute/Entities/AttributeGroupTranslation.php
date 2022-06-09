<?php

namespace Modules\Attribute\Entities;

use Illuminate\Database\Eloquent\Model;

class AttributeGroupTranslation extends Model
{
    protected $fillable = [ "store_id", "attribute_group_id", "name" ];
    public $timestamps = false;
}
