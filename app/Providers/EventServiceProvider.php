<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\User;
use App\Models\Store;
use App\Models\Order;
use App\Models\Product;
use App\Models\Location;
use App\Observers\CartObserver;
use App\Observers\UserObserver;
use App\Observers\OrderObserver;
use App\Observers\StoreObserver;
use App\Observers\ProductObserver;
use App\Observers\LocationObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        Cart::observe(CartObserver::class);
        Order::observe(OrderObserver::class);
        Store::observe(StoreObserver::class);
        Product::observe(ProductObserver::class);
        Location::observe(LocationObserver::class);
    }
}
