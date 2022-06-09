<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasFactory;
use Modules\Core\Traits\Sluggable;

class CacheManagement extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = "cache";

    public static array $SEARCHABLE = [
        "name",
        "slug",
        "description",
        "tag",
        "key",
    ];

    protected $fillable = [
        "name",
        "slug",
        "description",
        "tag",
        "key",
    ];
}
