<?php

namespace Modules\PaymentKlarna\Entities;

use Modules\Cart\Entities\Cart;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Website;
use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Customer\Entities\Customer;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlarnaOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        "cart_id",
        "website_id",
        "status",
        "channel_id",
        "store_id",
        "customer_id",
        "klarna_api_order_id",
        "base_url",
        "currency_code",
        "sub_total",
        "grand_total",
        "total_qty_ordered",
        "total_item_ordered",
        "customer_ip_address",
        "shipping_amount",
        "shipping_amount_tax",
        "coupon_code",
        "discount_amount",
        "discount_amount_tax",
        "tax_amount",
        "shipping_method",
        "shipping_method_label",
        "klarna_response",
        "sub_total_tax_amount",
        "weight",
        "store_name",
        "order_id",
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->with(["addresses"]);
    }

    public function order_items(): HasMany
    {
        return $this->hasMany(KlarnaOrderItem::class, "order_id");
    }

    public function order_taxes(): HasMany
    {
        return $this->hasMany(KlarnaOrderTax::class, "order_id");
    }
}
