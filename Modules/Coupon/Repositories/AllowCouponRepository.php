<?php

namespace Modules\Coupon\Repositories;

use Exception;
use Modules\Coupon\Entities\AllowCoupon;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;
use Modules\Coupon\Exceptions\AlreadyCreatedException;

class AllowCouponRepository extends BaseRepository
{
    public function __construct(AllowCoupon $allowCoupon)
    {
        $this->model = $allowCoupon;
        $this->model_key = "coupon-allow";

        $model_types_in = implode(",", config('model_list.model_types'));
        $this->rules = [
            "coupon_id" => "required|numeric",
            "model_type" => "required|in:{$model_types_in}",
            "model_id" => "required|numeric",
            "status" => "required|boolean"
        ];
    }

    public function allowedCouponExist(array $check_data): bool
    {
        try
        {
            $exists = $this->model->where($check_data)->exists();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $exists;
    }

    public function checkClass(string $class_name): void
    {
        try
        {
            if (!class_exists($class_name)) throw ValidationException::withMessages([ "model_type" => "Requested model '{$class_name}' does not exist." ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function validateAllowData(array $data, ?array $merge = []): array
    {
        try
        {
            $this->checkClass($data["model_type"]);
            $tableName = app($data["model_type"])->getTable();
            $merge = [ "model_id" => "required|numeric|exists:$tableName,id" ];
            $validator = Validator::make($data, array_merge($this->rules, $merge));
            if ( $validator->fails() ) throw ValidationException::withMessages($validator->errors()->toArray());
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $validator->validated();
    }

    public function getBulkData(object $request, object $coupon): array
    {
        try
        {
            $allow_data = [];
            foreach ($request->all() as $data) {
                foreach ($data["model_id"] as $model_id) {
                    $allow_data[] = array_merge($this->validateAllowData([
                        "coupon_id" => $coupon->id,
                        "model_type" => $data["model_type"],
                        "model_id" => $model_id,
                        "status" => $data["status"]
                    ]), [
                        "created_at" => now(),
                        "updated_at" => now()
                    ]);
                }
            }

            $filtered_data = array_filter($allow_data, function($data) {
                unset($data["created_at"]);
                unset($data["updated_at"]);
                return !$this->allowedCouponExist($data);
            });
            if ( count($filtered_data) == 0 ) throw new AlreadyCreatedException();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $filtered_data;
    }

    public function insertBulkData(array $data): bool
    {
        try
        {
            $inserted = $this->model::insert($data);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $inserted;
    }
}
