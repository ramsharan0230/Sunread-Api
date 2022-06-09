<?php

namespace Modules\Tax\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Traits\HasFactory;

class TaxRule extends Model
{
    use HasFactory;

    protected $fillable = [ "customer_group_class", "product_taxable_class", "name", "position", "priority", "subtotal", "status" ];

    public function customer_group(): BelongsTo
    {
        return $this->belongsTo(CustomerTaxGroup::class, "customer_group_class");
    }

    public function product_taxable(): BelongsTo
    {
        return $this->belongsTo(CustomerTaxGroup::class, "product_taxable_class");
    }

    public function tax_rates(): BelongsToMany
    {
        return $this->belongsToMany(TaxRate::class);
    }

    public function product_tax_groups(): BelongsToMany
    {
        return $this->belongsToMany(ProductTaxGroup::class);
    }

    public function customer_tax_groups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerTaxGroup::class);
    }
}
