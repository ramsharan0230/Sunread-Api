<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    public static $SEARCHABLE = [ 'code', 'name' ];
    protected $fillable = [ 'code', 'name', 'direction' ];
}
