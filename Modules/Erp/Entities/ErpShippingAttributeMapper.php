<?php

namespace Modules\Erp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Entities\Website;
use Modules\Core\Traits\HasFactory;

class ErpShippingAttributeMapper extends Model
{
    use HasFactory;

    protected $fillable = [
        "website_id",
        "shipping_agent_code",
        "shipping_agent_service_code",
        "shipping_method_code",
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
