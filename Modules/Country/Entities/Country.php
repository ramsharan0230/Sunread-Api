<?php

namespace Modules\Country\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [ "iso_2_code", "iso_3_code", "numeric_code", "dialing_code", "name" ];

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }
}