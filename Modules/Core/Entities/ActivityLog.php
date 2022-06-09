<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Traits\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    public static $SEARCHABLE = [ 'log_name', 'description' ,'activity','action' ];
    protected $fillable = [ 'log_name', 'description', 'subject_id', 'subject_type', 'causer_id', 'causer_type', 'properties', 'activity', 'action' ];
    protected $casts = [ 'properties' => 'array' ];

    // Polymorphic relationship for Subject
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // Polymorphic relationship for Causer
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

}
