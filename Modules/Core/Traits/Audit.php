<?php

namespace Modules\Core\Traits;


trait Audit {

    public function audits()
    {
        return $this->morphMany('App\Models\ActivityLog', 'subject');
    }

}
