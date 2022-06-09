<?php

namespace Modules\Sales\Scope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Modules\Sales\Entities\OrderStatusState;

class PendingOrderStatusScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $state_id = OrderStatusState::whereState("pending")->first()?->id;
        $builder->where("state_id", $state_id);
    }
}
