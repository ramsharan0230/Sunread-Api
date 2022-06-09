<?php

namespace Modules\Sales\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatusState extends Model
{
    use HasFactory;

    protected $fillable = ["name", "state", "is_default", "position"];

    public function order_statuses(): HasMany
    {
        return $this->hasMany(OrderStatus::class, "state_id");
    }
}
