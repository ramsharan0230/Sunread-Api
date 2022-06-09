<?php

namespace Modules\SizeChart\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeChartScope extends Model
{
    use HasFactory;

    protected $fillable = [
        "size_chart_id",
        "scope",
        "scope_id",
    ];

    public function size_chart(): BelongsTo
    {
        return $this->belongsTo(SizeChart::class);
    }

}