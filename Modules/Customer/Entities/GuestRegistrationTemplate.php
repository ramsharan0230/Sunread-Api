<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Customer\Scope\GuestRegistrationScope;

class GuestRegistrationTemplate extends Model
{
    protected $table = "email_templates";

    protected static function boot()
    {
        parent::boot();

        return static::addGlobalScope(new GuestRegistrationScope());
    }
}
