<?php

namespace Modules\Sales\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTax extends Model
{
    use HasFactory;

    protected $fillable = ["order_id", "code", "title", "percent", "amount"];
    
    public function order_tax_items(): HasMany
    {
        return $this->hasMany(OrderTaxItem::class, 'tax_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
