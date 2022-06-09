<?php

namespace Modules\Sales\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTransactionLog extends Model
{
    use HasFactory;

    protected $fillable = ["order_id", "amount", "currency", "ip_address", "request", "response", "response_code"];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
}
