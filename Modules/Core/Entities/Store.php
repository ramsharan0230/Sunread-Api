<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Traits\HasFactory;
use Modules\Core\Traits\Sluggable;

class Store extends Model
{
    use Sluggable, HasFactory;

    protected $fillable = [ "code", "name", "position", "status", "channel_id" ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}
