<?php

namespace Modules\Sales\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCart extends Model
{
    use HasFactory;

    protected $fillable = ["order_id", "cart_id"];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
