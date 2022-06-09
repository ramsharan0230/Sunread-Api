<?php

namespace Modules\Sales\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMeta extends Model
{
    use HasFactory;

    protected $fillable = ["order_id", "meta_key", "meta_value"];

    protected $casts = [
        'meta_value' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
}
