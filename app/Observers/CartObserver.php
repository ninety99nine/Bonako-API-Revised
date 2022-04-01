<?php

namespace App\Observers;

use App\Models\Cart;
use App\Repositories\CartRepository;

class CartObserver
{
    public function created(Cart $cart)
    {
        resolve(CartRepository::class)->setModel($cart)->createProductAndCouponLines();
    }

    public function updated(Cart $cart)
    {
        //
    }

    public function deleted(Cart $cart)
    {
        //
    }

    public function restored(Cart $cart)
    {
        //
    }

    public function forceDeleted(Cart $cart)
    {
    }
}
