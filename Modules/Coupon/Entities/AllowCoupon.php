<?php

namespace Modules\Coupon\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Traits\HasFactory;

class AllowCoupon extends Model
{
    use HasFactory;

    protected $fillable = ["coupon_id","model_type","model_id","status"];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
