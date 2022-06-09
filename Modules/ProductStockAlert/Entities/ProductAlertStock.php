<?php

namespace Modules\ProductStockAlert\Entities;

use Modules\Core\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAlertStock extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'email_address', 'product_id', 'store_id', 'send_count', 'send_date', 'status'];
    
}
