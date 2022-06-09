<?php

namespace Modules\Coupon\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = ["code","name","description","valid_from","valid_to","flat_discount_amount","min_discount_amount", "max_discount_amount",
        "discount_percent","max_uses","single_user_uses","only_new_user","min_purchase_amount","scope_public","status"];

    public function allowCoupons(): HasMany
    {
        return $this->hasMany(AllowCoupon::class);
    }

    public function scopePubliclyAvailable(object $query): object
    {
        $today = date('Y-m-d');
        return $query->where('valid_from','<=',$today)->where('valid_to','>=',$today)->whereStatus(1)->whereScopePublic(1);
    }

    public function scopePublished(object $query): object
    {
        return $query->whereStatus(1);
    }
}
