<?php

namespace Modules\User\Repositories;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\User\Entities\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Repositories\BaseRepository;
use Modules\User\Notifications\InvitationNotification;

class AdminRepository extends BaseRepository
{
    protected $main_image_dimensions, $gallery_image_dimensions;

    public function __construct(Admin $admin)
    {
        $this->model = $admin;
        $this->model_key = "admin";
        $this->main_image_dimensions = config('user_image.image_dimensions.user.main_image');
        $this->gallery_image_dimensions = config('user_image.image_dimensions.user.gallery_images');

        $this->rules = [
            "first_name" => "required|min:2|max:200",
            "last_name" => "required|min:2|max:200",
            "email" => "required|email|unique:admins,email",
            "current_password" => "sometimes|min:6|max:200",
            "password" => "sometimes|required|min:6|confirmed|max:200",
            "company" =>"sometimes|min:3|max:200",
            "address" =>"sometimes|min:3|max:200"
        ];
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
            $file_path = $file->storeAs("images/users/{$key}", $file_name, ["disk" => "public"]);

            $updated->fill(["profile_image" => $file_path]);
            $updated->save();

            // Store main_image and gallery_image variations
            foreach (["main_image_dimensions" => "main", "gallery_image_dimensions" => "gallery"] as $type => $folder) {
                foreach ($this->{$type} as $dimension) {
                    $width = $dimension["width"];
                    $height = $dimension["height"];
                    $path = "images/users/{$key}/{$folder}/{$width}x{$height}";
                    if(!Storage::has($path)) Storage::makeDirectory($path, 0777, true, true);

                    $image = Image::make($file)
                        ->fit($width, $height, function($constraint) {
                            $constraint->upsize();
                        })
                        ->encode('jpg', 80);
                    Storage::put("$path/{$file_name}", $image);
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

    public function validatePassword(object $request): array
    {
        $data = $request->validate([
            "current_password" => "required",
            "password" => "required|confirmed"
        ]);

        return $data;
    }
    
    public function storeInvitation(object $user): object
    {
        DB::beginTransaction();

        try
        {
            $user->password = Hash::make(Str::random(20));
            $user->invitation_token = $this->generateInvitationToken();
            $user->save();

            $user->notify(new InvitationNotification($user->invitation_token, $user?->role?->name));
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();

        return $user;
    }

    public function generateInvitationToken(): string
    {
        do {
            $token = Str::random(20);
        } while ($this->model->whereInvitationToken($token)->exists());

        return $token;
    }
}
