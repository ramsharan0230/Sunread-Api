<?php

namespace Modules\Tax\Entities;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Customer\Entities\CustomerGroup;

class CustomerTaxGroup extends Model
{
    use HasFactory;

    public static $SEARCHABLE = [ "name", "description" ];
    protected $fillable = [ "name", "description" ];

    public function tax_rules(): BelongsToMany
    {
        return $this->belongsToMany(TaxRule::class);
    }

    public function tax_group(): HasMany
    {
        return $this->hasMany(CustomerGroup::class);
    }

}
