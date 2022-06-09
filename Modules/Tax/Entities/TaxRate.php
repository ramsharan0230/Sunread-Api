<?php

namespace Modules\Tax\Entities;

use Modules\Core\Traits\HasFactory;
use Modules\Country\Entities\Region;
use Modules\Country\Entities\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaxRate extends Model
{
    use HasFactory;

    public static $SEARCHABLE = [ "identifier" ];
    protected $fillable = [ "country_id", "region_id", "identifier", "use_zip_range", "zip_code", "postal_code_from", "postal_code_to", "tax_rate" ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function tax_rules(): BelongsToMany
    {
        return $this->belongsToMany(TaxRule::class);
    }
}
