<?php

namespace Modules\Attribute\Entities;

use Illuminate\Database\Eloquent\Model;

class AttributeOptionTranslation extends Model
{
    protected $fillable = [ "store_id", "attribute_option_id", "name" ];
    public $timestamps = false;
}
