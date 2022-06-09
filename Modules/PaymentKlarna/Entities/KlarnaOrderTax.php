<?php

namespace Modules\PaymentKlarna\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlarnaOrderTax extends Model
{
    use HasFactory;

    protected $fillable = [ "order_id", "code", "title", "percent", "amount" ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(KlarnaOrder::class, "order_id");
    }

    public function order_tax_items(): HasMany
    {
        return $this->hasMany(KlarnaOrderTaxItem::class, "tax_id");
    }
}
