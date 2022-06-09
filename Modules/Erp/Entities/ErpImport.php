<?php

namespace Modules\Erp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ErpImport extends Model
{
    use HasFactory;

    protected $fillable = ["type", "status"];

    public function erp_import_details(): HasMany
    {
        return $this->hasMany(ErpImportDetail::class);
    }

}
