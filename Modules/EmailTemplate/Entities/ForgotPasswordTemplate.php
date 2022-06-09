<?php

namespace Modules\EmailTemplate\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\EmailTemplate\Scope\ForgotPasswordTemplateScope;

class ForgotPasswordTemplate extends Model
{
    protected $table = "email_templates";

    protected static function boot()
    {
        parent::boot();

        return static::addGlobalScope(new ForgotPasswordTemplateScope());
    }
}
