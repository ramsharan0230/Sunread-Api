<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\HasFactory;
use Modules\Core\Traits\Sluggable;
use Modules\Tax\Entities\CustomerTaxGroup;

class CustomerGroup extends Model
{
    use Sluggable, HasFactory;

    protected $fillable = [ "name", "slug", "is_user_defined", "customer_tax_group_id" ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function tax_group(): BelongsTo
    {
        return $this->belongsTo(CustomerTaxGroup::class, "customer_tax_group_id");
    }
}

