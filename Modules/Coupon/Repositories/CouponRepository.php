<?php

namespace Modules\Coupon\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Core\Repositories\BaseRepository;
use Modules\Coupon\Entities\Coupon;
use Exception;

class CouponRepository extends BaseRepository
{
    public function __construct(Coupon $coupon)
    {
        $this->model = $coupon;
        $this->model_key = "coupons";
        $this->rules = [
            "code" => "nullable|unique:coupons,code",
            "name" => "required",
            "description" => "sometimes|nullable",
            "valid_from" => "required|date",
            "valid_to" => "required|date|after:valid_from",
            "flat_discount_amount" => "sometimes|nullable|numeric",
            "min_discount_amount" => "sometimes|nullable|numeric",
            "max_discount_amount" => "sometimes|nullable|numeric",
            "min_purchase_amount" => "sometimes|nullable|numeric",
            "discount_percent" => "sometimes|nullable|numeric|between:0,99.99",
            "max_uses" => "required|numeric",
            "single_user_uses" => "sometimes|numeric",
            "only_new_user" => "sometimes|boolean",
            "scope_public" => "sometimes|boolean",
            "status" => "sometimes|boolean"
        ];
    }
}
