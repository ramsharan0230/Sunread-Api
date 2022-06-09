<?php

namespace Modules\Erp\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\CheckOutMethods\Entities\CheckOutMethod;
use Modules\Erp\Entities\ErpPaymentMethodMapper;
use Modules\Core\Repositories\BaseRepository;
use Exception;

class PaymentMethodMapperRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new ErpPaymentMethodMapper();
        $this->model_key = "erp.mappers.payment";

        $this->rules = [
            "website_id" => "required|numeric|exists:websites,id",
            "payment_method" => "required|string|max:255",
            "payment_method_code" => "required|string|max:255",
        ];
    }

    public function getAllPaymentMethodsSlug(): Collection
    {
        try
        {
            $paymentMethod = CheckOutMethod::where("type", CheckOutMethod::PAYMENT_METHOD);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $paymentMethod;
    }
}

