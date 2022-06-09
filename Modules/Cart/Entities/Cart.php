<?php

namespace Modules\Cart\Entities;

use Modules\Cart\Traits\HasUuid;
use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Customer\Entities\Customer;

class Cart extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['customer_id', 'item_count', 'total_quantity', 'coupon_code', 'channel_code', 'store_code'];

    public function cartItems(): HasMany
    {
       return $this->hasMany(CartItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
}
