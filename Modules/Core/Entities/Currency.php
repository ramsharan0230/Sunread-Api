<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\CurrencyExchangeRate;
use Modules\Core\Traits\HasFactory;

class Currency extends Model
{
    use HasFactory;
    public static $SEARCHABLE = [ 'code', 'name', 'symbol' ];
    protected $fillable = [ 'code', 'name', 'symbol','status' ];

    // Set Currency Code in Capitalized Letters
    public function setCodeAttribute($code)
    {
        $this->attributes['code'] = strtoupper($code);
    }

    // Get Exchange rate from associated Currency
    public function CurrencyExchangeRate()
    {
        return $this->hasOne(CurrencyExchangeRate::class, 'target_currency');
    }

    public function stores()
    {
        return $this->hasMany(Store::class, 'currency', 'code');
    }

    public function channels()
    {
        return $this->hasMany(Channel::class, 'default_currency', 'code');
    }
}
