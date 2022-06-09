<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class CurrencyExchangeRate extends Model
{
    protected $fillable = [ 'target_currency', 'rate' ];
}
