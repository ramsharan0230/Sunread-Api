<?php

namespace Modules\NavigationMenu\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationMenuItemValue extends Model
{
    use HasFactory;

    /**
     * Arrays that are mass assignable for NavigationMenuItemValue Model
     */
    protected $fillable = ['navigation_menu_item_id', 'scope', 'scope_id', 'attribute', 'value'];

    /**
     * Many to One Relation Between NavigationMenuItemValue and NavigationMenuItem
     * @return BelongsTo
     */
    public function navigationMenuItem(): BelongsTo
    {
        return $this->belongsTo(NavigationMenuItem::class);
    }
}
