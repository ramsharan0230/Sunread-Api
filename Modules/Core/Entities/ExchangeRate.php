<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasFactory;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [ 'source_currency', 'target_currency', 'rate' ];

    // Get From Currency
    public function source()
    {
        return $this->belongsTo(Currency::class, 'source_currency');
    }

    // Get To Currency
    public function target()
    {
        return $this->belongsTo(Currency::class, 'target_currency');
    }
}
