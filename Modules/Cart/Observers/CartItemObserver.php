<?php

namespace Modules\Cart\Observers;

use Modules\Cart\Entities\CartItem;
use Modules\Core\Facades\Audit;

class CartItemObserver
{
    public function created(CartItem $cartItem)
    {
        // Audit::log($cartItem, __FUNCTION__);
    }

    public function updated(CartItem $cartItem)
    {
        // Audit::log($cartItem, __FUNCTION__);
    }

    public function deleted(CartItem $cartItem)
    {
        // Audit::log($cartItem, __FUNCTION__);
    }
}
