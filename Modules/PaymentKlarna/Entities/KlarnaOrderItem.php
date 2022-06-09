<?php

namespace Modules\PaymentKlarna\Entities;

use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;
use Modules\Core\Traits\HasFactory;
use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlarnaOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        "order_id",
        "website_id",
        "channel_id",
        "store_id",
        "product_id",
        "qty",
        "name",
        "sku",
        "cost",
        "price",
        "row_total",
        "row_total_incl_tax",
        "tax_amount",
        "tax_percent",
        "discount_amount_tax",
        "discount_amount",
        "discount_percent",
        "coupon_code",
        "weight",
        "price_incl_tax",
        "row_weight",
        "product_type",
        "product_options",
    ];

    protected $casts = ['product_options' => 'array'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(KlarnaOrder::class, "order_id");
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
