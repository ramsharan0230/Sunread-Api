<?php

namespace Modules\Coupon\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Repositories\CouponRepository;
use Modules\Coupon\Transformers\CouponResource;
use Exception;

class CouponController extends BaseController
{
    private $repository;

    public function __construct(Coupon $coupon, CouponRepository $couponRepository)
    {
        $this->model = $coupon;
        $this->model_name = "Coupon";
        $this->repository = $couponRepository;
        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return CouponResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new CouponResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $this->validateListFiltering($request);
            $fetched = $this->getFilteredList($request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }


    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request);
            if(!$request->code){
                do
                {
                    $code = strtoupper(Str::random(rand(6,8)));
                    $coupon_code = $this->model->where('code', $code)->first();
                }
                while(!empty($coupon_code));
                $data['code'] = $code;
            }
            $created = $this->repository->create($data);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->model->findOrFail($id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang("fetch-success"));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request,[
                "code" => "nullable|unique:coupons,code,{$id}",
            ]);
            if(!$request->code){
                do
                {
                    $code = strtoupper(Str::random(rand(6,8)));
                    $coupon_code = $this->model->where('code', $code)->first();
                }
                while(!empty($coupon_code));
                $data['code'] = $code;
            }
            $updated = $this->repository->update($data, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("update-success"));
    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->repository->delete($id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang("delete-success"));
    }

    public function modelList(): JsonResponse
    {
        try
        {
            $fetched = config('model_list.model_types');
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang("fetch-success",["name"=>"Model List"]));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try
        {
            $updated = $this->repository->updateStatus($request, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("status-updated"));
    }
}
