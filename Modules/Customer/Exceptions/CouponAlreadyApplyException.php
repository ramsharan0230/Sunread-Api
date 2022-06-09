<?php

namespace Modules\Customer\Exceptions;

class CouponAlreadyApplyException extends  \Exception
{
    public function __construct()
    {
        parent::__construct("Coupon Already Applied");
    }
}
