<?php

namespace Modules\Erp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasFactory;

class NavErpOrderMapper extends Model
{
    use HasFactory;

    protected $fillable = [
        "website_id",
        "title",
        "country_id",
        "nav_customer_number",
        "shipping_account",
        "discount_account",
        "customer_price_group",
        "is_default",
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
