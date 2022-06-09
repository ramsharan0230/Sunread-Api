<?php

namespace Modules\NavigationMenu\Entities;

use Modules\Core\Traits\Sluggable;
use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class NavigationMenu.
 *
 * @package Modules\NavigationMenu\Entities
 *
 * @property integer id
 * @property string title
 * @property string slug
 * @property string location
 * @property integer status
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class NavigationMenu extends Model
{
    use HasFactory, Sluggable;

    /**
     * Arrays that are Mass Assignable
     */
    protected $fillable = ['title', 'slug', 'status', 'location', 'website_id'];


    // Searchable
    public static $SEARCHABLE = [];

    protected $casts = [];

    protected $with = [];


    /**
     * One to Many Relation Between NavigationMenu and NavigationMenuItem
     */
    public function navigationMenuItems(): HasMany
    {
        return $this->hasMany(NavigationMenuItem::class);
    }

    public function rootNavigationMenuItems(): HasMany
    {
        return $this->navigationMenuItems()->whereNull('parent_id')->orderBy('position');
    }
}
