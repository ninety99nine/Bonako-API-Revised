<?php

namespace App\Providers;

use App\Services\ShoppingCart\ShoppingCartService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'user' => 'App\Models\User',
            'order' => 'App\Models\Order'
        ]);

        //  The ShoppingCartService class must be instantiated once
        $this->app->singleton(ShoppingCartService::class, fn($app) => new ShoppingCartService);

        /*
         *  Disable Wrapping API Resources
         *
         *  If you would like to disable the wrapping of the outer-most resource, you may use the
         *  "withoutWrapping" method on the base resource class. Typically, you should call this
         *  method from your AppServiceProvider or another service provider that is loaded on
         *  every request to your application:
         *  Reference: https://laravel.com/docs/5.7/eloquent-resources#concept-overview
         *
         */
        JsonResource::withoutWrapping();
    }
}
