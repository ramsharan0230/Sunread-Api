<?php

namespace Modules\Cart\Observers;

use Modules\Cart\Entities\Cart;
use Modules\Core\Facades\Audit;

class CartObserver
{
    public function created(Cart $cart)
    {
        // Audit::log($cart, __FUNCTION__);
    }

    public function updated(Cart $cart)
    {
        // Audit::log($cart, __FUNCTION__);
    }

    public function deleted(Cart $cart)
    {
        // Audit::log($cart, __FUNCTION__);
    }
}
