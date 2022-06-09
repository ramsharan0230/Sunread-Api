<?php

namespace Modules\Core\Facades;
use Illuminate\Support\Facades\Facade;

class JobTracker extends Facade
{
    protected static function getFacadeAccessor() {
        return 'jobtracker';
    }
}
