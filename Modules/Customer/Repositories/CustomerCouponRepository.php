<?php

namespace Modules\Customer\Repositories;

use Carbon\Carbon;
use Modules\Coupon\Entities\AllowCoupon;
use Modules\Coupon\Entities\Coupon;
use Exception;
use Modules\Customer\Exceptions\CouponAlreadyApplyException;

class CustomerCouponRepository
{
    protected $model, $model_key, $allowCoupon;

    public function __construct(Coupon $coupon, AllowCoupon $allowCoupon)
    {
        $this->model = $coupon;
        $this->model_key = "customers.coupons";
        $this->allowCoupon = $allowCoupon;
    }


}
