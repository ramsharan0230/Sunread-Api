<?php

namespace Modules\Customer\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class GuestRegistrationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where("email_template_code", '=', "guest_registration");
    }
}
