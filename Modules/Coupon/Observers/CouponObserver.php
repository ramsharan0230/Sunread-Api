<?php

namespace Modules\Coupon\Observers;

use Modules\Core\Facades\Audit;
use Modules\Coupon\Entities\Coupon;

class CouponObserver
{
    public function created(Coupon $coupon)
    {
        Audit::log($coupon, __FUNCTION__);
    }

    public function updated(Coupon $coupon)
    {
        Audit::log($coupon, __FUNCTION__);
    }

    public function deleted(Coupon $coupon)
    {
        Audit::log($coupon, __FUNCTION__);
    }
}
