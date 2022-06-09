<?php

namespace Modules\Erp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasFactory;
use Modules\User\Entities\Admin;

class ErpLog extends Model
{
    use HasFactory;

    protected $fillable = [
        "website_id",
        "entity_type",
        "entity_id",
        "causer_type",
        "causer_id",
        "event",
        "resoponse_code",
        "request",
        "response",
        "type",
    ];

    const WEBHOOK = "webhook";
    const USER = "user";
    const SYSTEM = "system";

    public static $causerTypes = [
        self::WEBHOOK,
        self::USER,
        self::SYSTEM,
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
