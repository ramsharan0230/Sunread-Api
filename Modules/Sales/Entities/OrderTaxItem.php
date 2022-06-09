<?php

namespace Modules\Sales\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTaxItem extends Model
{
    use HasFactory;

    protected $fillable = ["tax_id", "item_id", "tax_percent", "amount", "tax_item_type"];

    public function order_tax(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
}
