<?php

namespace Modules\Sales\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    use HasFactory;

    protected $fillable = ["causer_type", "causer_id", "order_id", "title", "data", "action"];
}
