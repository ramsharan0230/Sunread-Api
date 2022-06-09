<?php

namespace Modules\Review\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasFactory;

class ReviewReply extends Model
{
    use HasFactory;

    protected $fillable = [ "review_id", "description" ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

}
