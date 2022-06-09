<?php

namespace Modules\EmailTemplate\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\EmailTemplate\Scope\NewOrderTemplateScope;

class NewOrderTemplate extends Model
{
    protected $table = "email_templates";

    protected static function boot()
    {
        parent::boot();

        return static::addGlobalScope(new NewOrderTemplateScope());
    }
}
