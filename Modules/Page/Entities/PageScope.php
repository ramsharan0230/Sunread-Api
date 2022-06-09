<?php

namespace Modules\Page\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageScope extends Model
{
    use HasFactory;

    protected $fillable = [ "page_id", "scope", "scope_id" ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

}
