<?php

namespace Modules\Sales\Entities;

use Modules\Core\Traits\Sluggable;
use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = ["name", "slug", "state_id"];

    public function order_status_state(): BelongsTo
    {
        return $this->belongsTo(OrderStatusState::class, "state_id");
    }

    public function status_siblings(): HasMany
    {
        return $this->hasMany(static::class, "state_id");
    }
}
