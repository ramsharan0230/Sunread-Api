<?php

namespace Modules\Country\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    protected $fillable = [ "region_id", "postal_code", "code", "name" ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
