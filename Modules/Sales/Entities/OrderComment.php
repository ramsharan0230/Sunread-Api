<?php

namespace Modules\Sales\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Entities\Admin;

class OrderComment extends Model
{
    use HasFactory;

    protected $fillable = [
        "order_id",
        "user_id",
        "is_customer_notified",
        "is_visible_on_storefornt",
        "comment",
        "status_flag",
    ];

    const STATUS_INFO = "info";
    const STATUS_ERROR = "error";
    const STATUS_SUCCESS = "success";
    const STATUS_WARNING = "warning";

    public static $status_flags = [
        self::STATUS_INFO,
        self::STATUS_ERROR,
        self::STATUS_SUCCESS,
        self::STATUS_WARNING,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, "user_id");
    }
}
