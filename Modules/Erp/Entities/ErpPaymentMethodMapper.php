<?php

namespace Modules\Erp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Entities\Website;
use Modules\Core\Traits\HasFactory;

class ErpPaymentMethodMapper extends Model
{
    use HasFactory;

    protected $fillable = [
        "website_id",
        "payment_method",
        "payment_method_code",
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
