<?php

namespace Modules\PaymentKlarna\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlarnaOrderTaxItem extends Model
{
    use HasFactory;

    protected $fillable = [ "tax_id", "item_id", "percent", "amount" ];

    public function order_tax(): BelongsTo
    {
        return $this->belongsTo(KlarnaOrderTax::class, "tax_id");
    }
}
