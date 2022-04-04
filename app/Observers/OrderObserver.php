<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    /**
     *  The saving event will dispatch when a model is created or updated
     *  even if the model's attributes have not been changed.
     *
     *  Refererence: https://laravel.com/docs/9.x/eloquent#events
     */
    public function saving(Order $order)
    {
        return $order->generateConfirmationCode();
    }

    public function created(Order $order)
    {
        //
    }

    public function updated(Order $order)
    {
        //
    }

    public function deleted(Order $order)
    {
        //
    }

    public function restored(Order $order)
    {
        //
    }

    public function forceDeleted(Order $order)
    {
    }
}
