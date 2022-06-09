<?php

namespace Modules\Coupon\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Coupon\Entities\AllowCoupon;
use Exception;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Exceptions\AlreadyCreatedException;
use Modules\Coupon\Repositories\AllowCouponRepository;
use Modules\Coupon\Transformers\AllowCouponResource;

class AllowCouponController extends BaseController
{
    private $coupon, $repository;

    public function __construct(AllowCoupon $allowCoupon, Coupon $coupon, AllowCouponRepository $allowCouponRepository)
    {
        $this->model = $allowCoupon;
        $this->model_name = "Allow Coupon";
        $this->coupon = $coupon;
        $this->repository = $allowCouponRepository;

        $exception_statuses = [
            AlreadyCreatedException::class => 409
        ];

        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function collection(object $data): ResourceCollection
    {
        return AllowCouponResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new AllowCouponResource($data);
    }

    public function allowCoupon(Request $request, int $coupon_id): JsonResponse
    {
        try
        {
            $coupon = $this->coupon->whereId($coupon_id)->published()->firstOrFail();

            $data = $this->repository->getBulkData($request, $coupon);
            $this->repository->insertBulkData($data);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('create-success'), 201);
    }

    public function deleteAllowCoupon(Request $request): JsonResponse
    {
        try
        {
            $this->repository->bulkDelete($request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }
}
