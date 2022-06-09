<?php

namespace Modules\Customer\Repositories\StoreFront;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Facades\SiteConfig;
use Modules\Customer\Entities\Customer;
use Modules\Core\Repositories\BaseRepository;
use Modules\Customer\Entities\CustomerGroup;
use Modules\Notification\Events\ConfirmEmail;
use Modules\Notification\Events\NewAccount;
use Modules\Notification\Events\RegistrationSuccess;

class CustomerRepository extends BaseRepository
{
    protected $main_image_dimensions;
    protected $gallery_image_dimensions;

    public function __construct()
    {
        $this->model = new Customer();
        $this->model_key = "customers.customers";
        $this->main_image_dimensions = config('customer_image.image_dimensions.user.main_image');
        $this->gallery_image_dimensions = config('customer_image.image_dimensions.user.gallery_images');

        $this->rules = [
            "first_name" => "required|min:2|max:200",
            "middle_name" => "sometimes|nullable|min:2|max:200",
            "last_name" => "required|min:2|max:200",
            "email" => "required|email|unique:customers,email",
            "gender" => "required|in:male,female,other",
            "date_of_birth" => "date|before:today",
            "subscribed_to_news_letter" => "sometimes|boolean",
            "phone" => "nullable"
        ];
    }

    public function registration(object $request): array
    {
        try
        {
            $data = $this->validateData($request, [
                "password" => "required|min:6|confirmed",
                "website_id" => "required|exists:websites,id",
                "store_id" => "required|exists:stores,id",
            ], function() {
                return [
                    "status" => 1,
                    "is_lock" => 0,
                ];
            });

            $customer_group_id = $this->getCustomerGroupId();
            if(is_null($request->customer_group_id)) $data["customer_group_id"] = $customer_group_id;

            $data["password"] = Hash::make($request->password);
            if(SiteConfig::fetch("require_email_confirmation", "website", $request->website_id) == 1) {
                $data["verification_token"] = Str::random(30);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }
    public function updateAccount(object $request, object $customer): array
    {
        try
        {
            $merge = array_merge($this->getPasswordRules($request), [
                "email" => "required|email|unique:customers,email,{$customer->id}"
            ]);

            $customer_group_id = $this->getCustomerGroupId();

            $data = $this->validateData($request, $merge, function () use($customer, $customer_group_id) {
                return [
                    "website_id" => $customer->website_id,
                    "store_id" => $customer->store_id,
                    "status" => 1,
                    "is_lock" => 1,
                    "customer_group_id" => $customer_group_id,
                    "email" => $customer->email
                ];
            });

            if ( isset($request->current_password) ||  isset($request->password) ) $data["password"] = $this->getPassword($request);
            unset($data["current_password"]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getCustomerGroupId(): ?int
    {
        return CustomerGroup::whereSlug("general")->first()?->id ?? null;
    }

    public function getPasswordRules(object $request) : array
    {
        $merge_rule = [];
        if ( isset($request->current_password) ||  isset($request->password) ) {
            $merge_rule = [
                "current_password" => "required|min:6|max:200",
                "password" => "required|min:6|confirmed"
            ];
        }
        return $merge_rule;
    }

    public function getPassword(object $request) : ?string
    {
        if (!Hash::check($request->current_password, auth()->guard('customer')->user()->password)) {
            throw new Exception("Password is incorrect.");
        }
        return Hash::make($request->password);
    }

    public function uploadProfileImage(object $request, int $id): object
    {
        DB::beginTransaction();
        Event::dispatch("{$this->model_key}.profile_image.update.before");

        try
        {
            $request->validate([
                'image' => 'required|mimes:jpeg,jpg,png',
            ]);
            $updated = $this->model->findOrFail($id);

            // Store File
            $file = $request->file("image");
            $key = Str::random(6);
            $file_name = $this->generateFileName($file);
            $file_path = $file->storeAs("images/customers/{$key}", $file_name, ["disk" => "public"]);
            $updated->fill(["profile_image" => $file_path]);
            $updated->save();
            // Store main_image and gallery_image variations
            foreach (["main_image_dimensions" => "main", "gallery_image_dimensions" => "gallery"] as $type => $folder) {
                foreach ($this->{$type} as $dimension) {
                    $width = $dimension["width"];
                    $height = $dimension["height"];
                    $path = "images/customers/{$key}/{$folder}/{$width}x{$height}";
                    if(!Storage::has($path)) Storage::makeDirectory($path, 0777, true, true);

                    $image = Image::make($file)
                        ->fit($width, $height, function($constraint) {
                            $constraint->upsize();
                        })
                        ->encode('jpg', 80);
                    Storage::put("{$path}/{$file_name}", $image);
                }
            }
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        Event::dispatch("{$this->model_key}.profile_image.update.after", $updated);
        DB::commit();

        return $updated;
    }

    public function removeOldImage(int $id): object
    {
        DB::beginTransaction();
        Event::dispatch("{$this->model_key}.delete.profile_image.before");

        try
        {
            $updated = $this->model->findOrFail($id);
            if (!$updated->profile_image) {
                DB::commit();
                return $updated;
            }

            $path_array = explode("/", $updated->profile_image);
            unset($path_array[count($path_array) - 1]);

            $delete_folder = implode("/", $path_array);
            Storage::disk("public")->deleteDirectory($delete_folder);

            $updated->fill(["profile_image" => null]);
            $updated->save();
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        Event::dispatch("{$this->model_key}.delete.profile_image.after", $updated);
        DB::commit();

        return $updated;
    }

    public function sendRegistrationEmail(object $customer, object $request): bool
    {
        try
        {
            event(new RegistrationSuccess($customer));
            $required_email_confirm = SiteConfig::fetch("require_email_confirmation", "website", $request->website_id);
            if($required_email_confirm == 1) {
                event(new NewAccount($customer));
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;
    }

    public function sendVerificationLink(object $customer): bool
    {
        try
        {
            $required_email_confirm = SiteConfig::fetch("require_email_confirmation", "website", $customer->website_id);
            if($required_email_confirm == 1) {
                $customer["verification_token"] = Str::random(30);
                $customer->save();

                event(new NewAccount($customer));
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;
    }

    public function verifyCustomerAccount(object $customer): bool
    {
        try
        {
            $customer->is_email_verified = 1;
            $customer->verification_token = null;
            $customer->save();

            event(new ConfirmEmail($customer));
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;
    }
}
