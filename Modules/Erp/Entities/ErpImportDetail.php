<?php

namespace Modules\Erp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErpImportDetail extends Model
{
    use HasFactory;

    protected $fillable = [ "erp_import_id", "sku", "value", "hash" ];

    protected $casts = [
        'value' => 'array'
    ];

    public function erp_import(): BelongsTo
    {
        return $this->belongsTo(ErpImport::class);
    }
}
