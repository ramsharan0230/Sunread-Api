<?php

namespace Modules\EmailTemplate\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrderUpdateGuestTemplateScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where("email_template_code", '=', "order_update_guest");
    }
}
