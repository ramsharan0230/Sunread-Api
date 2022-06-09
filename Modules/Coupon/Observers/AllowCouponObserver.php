<?php

namespace Modules\Coupon\Observers;

use Modules\Core\Facades\Audit;
use Modules\Coupon\Entities\AllowCoupon;

class AllowCouponObserver
{
    public function created(AllowCoupon $allowCoupon)
    {
        Audit::log($allowCoupon, __FUNCTION__);
    }

    public function updated(AllowCoupon $allowCoupon)
    {
        Audit::log($allowCoupon, __FUNCTION__);
    }

    public function deleted(AllowCoupon $allowCoupon)
    {
        Audit::log($allowCoupon, __FUNCTION__);
    }
}
