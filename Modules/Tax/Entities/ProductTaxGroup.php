<?php

namespace Modules\Tax\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductTaxGroup extends Model
{
    use HasFactory;

    public static $SEARCHABLE = [ "name", "description" ];
    protected $fillable = [ "name", "description" ];

    public function tax_rules(): BelongsToMany
    {
        return $this->belongsToMany(TaxRule::class);
    }

    
}
