<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Modules\User\Notifications\ResetPasswordNotification;
use phpDocumentor\Reflection\Types\Boolean;

class Admin extends Authenticatable implements JWTSubject
{
    use Notifiable, HasFactory;

    public static $SEARCHABLE = [ "first_name", "email" ];
    protected $fillable = [ "first_name", "last_name", "email", "password", "api_token", "role_id", "status", "company", "address", "profile_image", "invitation_token" ];
    protected $hidden = [ "password", "api_token", "remember_token" ];
    protected $appends = [ "avatar", "profile_image_url" ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role->permission_type == 'custom' && ! $this->role->permissions) return false;

        return in_array($permission, $this->role->permissions);
    }

    public function getJWTIdentifier(): ?string
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
        // TODO: Implement getJWTCustomClaims() method.
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function hasRole(string $roleSlug): bool
    {
        if (empty($roleSlug) || empty($this->role)) return false;
        if ($this->role->slug == $roleSlug) return true;

        return false;
    }

    public function getAvatarAttribute(): ?string
    {
        return $this->getImage();
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->profile_image ? Storage::url($this->profile_image) : null;
    }

    public function getImage($image_type = "main_image"): ?string
    {
        if ( !$this->profile_image ) return null;
        $image_url = null;

        switch ($image_type){
            case 'main_image':
                $image_url = $this->getDimensionPath("user_image.image_dimensions.user.main_image");
                break;

            case 'gallery_image':
                $image_url = $this->getDimensionPath("user_image.image_dimensions.user.gallery_images");
                break;
        }

        return $image_url;
    }

    private function getDimensionPath(string $config, string $folder = "main"): string
    {
        $dimension = config($config)[0];
        $width = $dimension["width"];
        $height = $dimension["height"];

        $file_array = $this->getFileNameArray();
        return Storage::url("{$file_array['folder']}/{$folder}/{$width}x{$height}/{$file_array['file']}");
    }

    private function getFileNameArray(): array
    {
        $path_array = explode("/", $this->profile_image);
        $file_name = $path_array[count($path_array) - 1];
        unset($path_array[count($path_array) - 1]);

        return [
            "folder" => implode("/", $path_array),
            "file" => $file_name
        ];
    }

    public function getFullNameAttribute(): string
    {
        return ucwords("{$this->first_name} {$this->last_name}");
    }

}
