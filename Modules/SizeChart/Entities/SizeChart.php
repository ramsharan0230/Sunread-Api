<?php

namespace Modules\SizeChart\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Entities\Website;
use Modules\Core\Traits\HasFactory;
use Modules\Core\Traits\Sluggable;
use Modules\SizeChart\Entities\SizeChartScope;

class SizeChart extends Model
{
    use HasFactory;
    use Sluggable;

    protected $fillable = [
        "slug",
        "title",
        "content",
        "status",
        "website_id",
    ];

    public function size_chart_scopes(): HasMany
    {
        return $this->hasMany(SizeChartScope::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function size_chart_contents(): HasMany
    {
        return $this->hasMany(SizeChartContent::class);
    }
}
