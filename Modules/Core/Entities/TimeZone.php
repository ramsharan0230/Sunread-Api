<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeZone extends Model
{
    use HasFactory;

    protected $fillable = ["time_zone", "name", "zone"];
}
