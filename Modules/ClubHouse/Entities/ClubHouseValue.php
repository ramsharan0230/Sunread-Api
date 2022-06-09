<?php

namespace Modules\ClubHouse\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class ClubHouseValue.
 *
 * @package Modules\ClubHouse\Entities
 *
 * @property integer id
 * @property integer club_house_id
 * @property string scope
 * @property integer scope_id
 * @property string attribute
 * @property string value
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class ClubHouseValue extends Model
{
    use HasFactory;

    /**
     * Arrays that are mass assignable for ClubHousValue Model
     */
    protected $fillable = ['club_house_id', 'scope', 'scope_id', 'attribute', 'value'];

    /**
     * Many to One Relation Between ClubHouseValue and ClubHouse
     * @return BelongsTo
     */
    public function clubHouse(): BelongsTo
    {
        return $this->belongsTo(ClubHouse::class);
    }

}
