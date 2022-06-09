<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobTracker extends Model
{
    use HasFactory;

    protected $fillable = [ "name", "total_jobs", "completed_jobs", "failed_jobs", "status"];
    
}
