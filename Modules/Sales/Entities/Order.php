<?php

namespace Modules\Sales\Entities;

use Modules\Core\Entities\Website;
use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Customer\Entities\Customer;
use Modules\Sales\Entities\OrderAddress;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        "website_id",
        "channel_id",
        "store_id",
        "cart_id",
        "customer_id",
        "website_name",
        "channel_name",
        "store_name",
        "is_guest",
        "billing_address_id",
        "shipping_address_id",
        "shipping_method",
        "shipping_method_label",
        "payment_method",
        "payment_method_label",
        "currency_code",
        "coupon_code",
        "discount_amount",
        "discount_amount_tax",
        "shipping_amount",
        "shipping_amount_tax",
        "sub_total",
        "sub_total_tax_amount",
        "tax_amount",
        "grand_total",
        "weight",
        "total_items_ordered",
        "total_qty_ordered",
        "customer_email",
        "customer_first_name",
        "customer_middle_name",
        "customer_last_name",
        "customer_phone",
        "customer_taxvat",
        "customer_ip_address",
        "status",
        "external_erp_id",
    ];

    public static $SEARCHABLE = [
        "id",
        "store_name",
        "customer_first_name",
        "customer_middle_name",
        "customer_last_name",
        "customer_phone",
        "status",
        "created_at",
    ];

    public function order_addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function order_comments(): HasMany
    {
        return $this->hasMany(OrderComment::class);
    }

    public function order_items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function order_metas(): HasMany
    {
        return $this->hasMany(OrderMeta::class);
    }

    public function order_taxes(): HasMany
    {
        return $this->hasMany(OrderTax::class);
    }

    public function order_tax_items(): HasManyThrough
    {
        return $this->hasManyThrough(OrderTaxItem::class, OrderTax::class, "order_id", "tax_id");
    }

    public function order_transactions(): HasMany
    {
        return $this->hasMany(OrderTransactionLog::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, "website_id");
    }

    public function billing_address(): BelongsTo
    {
        return $this->belongsTo(OrderAddress::class, "billing_address_id")->with(["country", "city", "region"]);
    }

    public function shipping_address(): BelongsTo
    {
        return $this->belongsTo(OrderAddress::class, "shipping_address_id")->with(["country", "city", "region"]);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, "customer_id");
    }

    public function order_status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, "status", "slug");
    }
}
