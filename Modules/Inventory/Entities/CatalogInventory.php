<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Inventory\Entities\CatalogInventoryItem;
use Modules\Core\Traits\HasFactory;
use Modules\Product\Entities\Product;
use Modules\Core\Entities\Website;

class CatalogInventory extends Model
{
    use HasFactory;

    protected $fillable = ["product_id", "website_id", "quantity", "is_in_stock", "manage_stock", "use_config_manage_stock"];

    public function catalog_inventory_items(): HasMany
    {
        return $this->hasMany(CatalogInventoryItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class); 
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
