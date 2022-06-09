<?php

namespace Modules\Erp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Entities\Website;
use Modules\Core\Traits\HasFactory;

class ErpWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        "website_id",
        "entity_type",
        "entity_id",
        "payload",
        "is_processing",
        "status",
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
