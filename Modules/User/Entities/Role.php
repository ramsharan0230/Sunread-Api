<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\HasFactory;
use Modules\Core\Traits\Sluggable;

class Role extends Model
{
    use Sluggable, HasFactory;

    public static $SEARCHABLE = [ "name", "description", "permissions", "permission_type", "slug" ];
    protected $fillable = [ "name", "description", "permission_type", "permissions", "slug" ];
    protected $casts = [ "permissions" => "array" ];

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }
}
