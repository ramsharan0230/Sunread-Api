<?php

namespace Modules\SizeChart\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasFactory;
use Modules\Core\Traits\Sluggable;

class SizeChartContent extends Model
{
    use HasFactory;
    use Sluggable;

    protected $fillable = [
        "title",
        "slug",
        "type",
        "content",
        "size_chart_id",
    ];

    const SIZE_CHART_CONTENT_TYPE_IMAGE = "image";
    const SIZE_CHART_CONTENT_TYPE_EDITOR = "editor";

    public static $content_types = [
        self::SIZE_CHART_CONTENT_TYPE_IMAGE,
        self::SIZE_CHART_CONTENT_TYPE_EDITOR,
    ];

    public function size_chart(): BelongsTo
    {
        return $this->belongsTo(SizeChart::class);
    }
}
