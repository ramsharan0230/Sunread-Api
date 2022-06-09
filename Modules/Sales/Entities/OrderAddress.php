<?php

namespace Modules\Sales\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Country\Entities\City;
use Modules\Country\Entities\Country;
use Modules\Country\Entities\Region;

class OrderAddress extends Model
{
    use HasFactory;

    protected $fillable = ["order_id", "customer_id", "customer_address_id", "address_type", "first_name", "middle_name", "last_name", "phone", "email", "address1", "address2", "address3", "postcode", "country_id", "region_id", "city_id", "region_name", "city_name", "vat_number"];

    const BILLING_ADDRESS = "shipping";
    const SHIPPING_ADDRESS = "billing";

    public static $address_types = [
        self::BILLING_ADDRESS,
        self::SHIPPING_ADDRESS,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function getFullNameAttribute(): ?string
    {
        return ucwords(preg_replace('/\s+/', ' ', "{$this->first_name} {$this->middle_name} {$this->last_name}"));
    }
}
