<?php

namespace Modules\NavigationMenu\Entities;

use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Support\Collection;
use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Modules\NavigationMenu\Traits\HasScope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationMenuItem extends Model
{
    use HasFactory, HasScope, NodeTrait;

    /**
     * Arrays that are Mass Assignable
     */
    protected $fillable = ["navigation_menu_id", "parent_id", "position"];

    // Append data with Values [NavigationMenuItem]
    protected $with = [ "values" ];

    // Searchable
    public static $SEARCHABLE = [];

    protected $casts = [];

    public function getRootNavigationMenus(): Collection
    {
        return NavigationMenuItem::where('parent_id', null)->get();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavigationMenuItem::class, 'parent_id');
    }

    public function getNavigationMenuItemTree(): Collection
    {
        return $this->id
            ? $this::orderBy('position', 'ASC')->where('id', '!=', $this->id)->get()->toTree()
            : $this::orderBy('position', 'ASC')->get()->toTree();
    }

    /**
     * Get Image URL
     */
    public function image_url(): ?string
    {
        if (!$this->image) return null;
        return Storage::url($this->image);
    }

    /**
     * Get Image URL Attribute
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_url();
    }

    /**
     * One to Many Relation Between NavigationMenuItem and NavigationMenuItemValue
     */
    public function values(): HasMany
    {
        return $this->hasMany(NavigationMenuItemValue::class);
    }

    /**
     * Many to One Relation Between NavigationMenuItem and NavigationMenu
     */
    public function navigationMenu(): BelongsTo
    {
        return $this->belongsTo(NavigationMenu::class);
    }
}
