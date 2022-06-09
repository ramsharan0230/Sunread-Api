<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Sales\Scope\PendingOrderStatusScope;

class PendingOrderStatus extends Model
{
    protected $table = "order_statuses";

    protected static function boot()
    {
        parent::boot();
        return static::addGlobalScope(new PendingOrderStatusScope());
    }
}
