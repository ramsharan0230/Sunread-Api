<?php

namespace Modules\Customer\Repositories\StoreFront;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Facades\SiteConfig;
use Modules\Customer\Entities\Customer;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;
use Modules\Customer\Events\GuestRegistration;
use Modules\Notification\Events\RegistrationSuccess;
use Modules\Core\Repositories\ConfigurationRepository;
use Modules\Customer\Repositories\CustomerGroupRepository;

class CustomerCheckoutRepository extends BaseRepository
{
    private $customerGroupRepository;

    public function __construct()
    {
        $this->model = new Customer();
        $this->customerGroupRepository = new CustomerGroupRepository();
        $this->model_key = "customers.customers";
        $this->rules = [
            "first_name" => "required|min:2|max:200",
            "middle_name" => "sometimes|nullable|min:2|max:200",
            "last_name" => "required|min:2|max:200",
            "email" => "required|email|unique:customers,email",
            "gender" => "sometimes|in:male,female,other",
            "date_of_birth" => "date|before:today",
            "subscribed_to_news_letter" => "sometimes|boolean",
            "phone" => "nullable",
        ];
    }

    public function registration(array $customer_data): object
    {
        DB::beginTransaction();
        try
        {
            $this->validateRegistration($customer_data);

            $data = array_merge($customer_data, [
                        "status" => 1,
                        "is_lock" => 0,
                    ]);
            $customer_group_id = $this->getCustomerGroupId();
            if (isset($customer_data["customer_group_id"])) {
                $data["customer_group_id"] = $customer_group_id;
            }

            $data["password"] = Hash::make($customer_data["password"]);
            if (SiteConfig::fetch("require_email_confirmation", "website", $customer_data["website_id"]) == 1) {
                $data["verification_token"] = Str::random(30);
            }

            $customer = $this->create($data);
            $this->sendRegistrationEmail($customer, $data);
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
        return $customer;
    }

    public function getCustomerGroupId(): ?int
    {
        return $this->customerGroupRepository->queryFetch(["slug" => "general"])?->id;
    }

    public function sendRegistrationEmail(object $customer, array $data): void
    {
        try
        {
            event(new RegistrationSuccess($customer));
            $required_email_confirm = SiteConfig::fetch("require_email_confirmation", "website", $data["website_id"]);
            if ($required_email_confirm == 1) {
                event(new GuestRegistration($customer, $data["password_confirmation"]));
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    public function validateRegistration(array $request): void
    {
        try
        {
            $this->rules = array_merge($this->rules, [
                "password" => "required|min:6|confirmed",
                "website_id" => "required|exists:websites,id",
                "store_id" => "required|exists:stores,id",
            ]);

            $validator = Validator::make($request, $this->rules);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}