<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

/**
 *  Location Routes (Public & Super-Admin Facing Routes)
 *
 *  Public users are allowed to view a location and its products
 */
Route::controller(LocationController::class)
    ->middleware('superadmin')
    ->prefix('locations')
    ->group(function () {

    Route::get('/', 'index')->name('locations.show');

    Route::prefix('{location}')->name('location')->group(function () {

        //  Allow the public access to the location
        Route::get('/', 'show')->name('.show')->whereNumber('location')
             ->withoutMiddleware('superadmin');

        Route::put('/', 'update')->name('.update')->whereNumber('location');
        Route::delete('/', 'delete')->name('.delete')->whereNumber('location');
        Route::post('/confirm-delete', 'confirmDelete')->name('.confirm.delete')->whereNumber('location');

        Route::get('/orders', 'orders')->name('.orders.show')->whereNumber('location');

        //  Allow the public access to the location products
        Route::get('/products', 'products')->name('.products.show')->whereNumber('location')
             ->withoutMiddleware('superadmin');

        Route::get('/customers', 'customers')->name('.customers.show')->whereNumber('location');
        Route::get('/users', 'showTeamMembers')->name('.team.members.show')->whereNumber('location');
        Route::post('/users', 'inviteTeamMembers')->name('.team.members.show')->whereNumber('location');
        Route::get('/instant-carts', 'instantCarts')->name('.instant.carts.show')->whereNumber('location');

    });

});

/**
 *  Location Routes
 *
 *  The following routes require an authenticated user that accepted Terms And Conditions
 */
Route::controller(LocationController::class)
    ->middleware('accepted.terms.and.conditions')
    ->prefix('auth/user/locations/{location}')
    ->name('auth.user.location')
    /**
     *  Scope bindings will instruct laravel to fetch the child relationship
     *  via the parent relationship e.g "locations/{location}/users/{user}"
     *  will make sure that the {user} must be a resource related to the
     *  {location} provided.
     *
     *  Refer to: https://laravel.com/docs/9.x/routing#implicit-model-binding-scoping
     */
    ->scopeBindings()
    ->group(function () {

    Route::get('/', 'show')->name('.show')->whereNumber('location');
    Route::put('/', 'update')->name('.update')->middleware('location.permission:manage settings')->whereNumber('location');
    Route::delete('/', 'delete')->name('.delete')->middleware('location.permission:manage settings')->whereNumber('location');
    Route::post('/confirm-delete', 'confirmDelete')->middleware('location.permission:manage settings')->name('.confirm.delete')->whereNumber('location');

    Route::get('/orders', 'showOrders')->name('.orders.show')->middleware('location.permission:manage orders')->whereNumber('location');
    Route::get('/products', 'showProducts')->name('.products.show')->middleware('location.permission:manage products')->whereNumber('location');
    Route::post('/products', 'createProduct')->name('.products.create')->middleware('location.permission:manage products')->whereNumber('location');
    Route::get('/customers', 'showCustomers')->name('.customers.show')->middleware('location.permission:manage customers')->whereNumber('location');
    Route::get('/instant-carts', 'instantCarts')->name('.instant.carts.show')->middleware('location.permission:manage instant carts')->whereNumber('location');

    //  Team Members
    Route::middleware('location.permission:manage team members')->group(function () {
        Route::get('/users', 'showTeamMembers')->name('.team.members.show')->whereNumber('location');
        Route::post('/users', 'inviteTeamMembers')->name('.team.members.invite')->whereNumber('location');

        //  Users can accept or decline invitations without the needed permission to manage team members
        Route::withoutMiddleware('location.permission:manage team members')->group(function () {
            Route::get('/permissions', 'showMyPermissions')->name('.permissions.show')->whereNumber('location');
            Route::post('/accept-invitation', 'acceptInvitation')->name('.accept.invitation')->whereNumber('location');
            Route::post('/decline-invitation', 'declineInvitation')->name('.decline.invitation')->whereNumber('location');
        });

        Route::prefix('/users/{user}')->name('.team.member')->group(function () {
            Route::get('/', 'showTeamMember')->name('.show')->whereNumber('location');
            Route::post('/update-permissions', 'updateTeamMemberPermissions')->name('.update.permissions')->whereNumber('location');
        });
    });

    /**
     *  Shopping Cart
     *
     *  Managing a shopping cart on this location does not require the
     *  customer to accept the terms and conditions. They are just
     *  simply shopping to place an order.
     */
    Route::withoutMiddleware('accepted.terms.and.conditions')->prefix('carts')->group(function () {

        Route::post('/', 'createShoppingCart')->name('.carts.create')->whereNumber('location');
        Route::put('/{cart}', 'updateShoppingCart')->name('.cart.update')->whereNumber('location');

        //Route::post('/', 'resetShoppingCart')->name('.reset')->whereNumber('location');
        //Route::post('/', 'refreshShoppingCart')->name('.refresh')->whereNumber('location');
    });

});
