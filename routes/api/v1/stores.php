<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;

/**
 *  Store Routes (Public & Super-Admin Facing Routes)
 *
 *  Public users are allowed to view a store and its locations
 */
Route::controller(StoreController::class)
    ->middleware('superadmin')
    ->prefix('stores')
    ->group(function () {

    Route::name('stores')->group(function () {
        Route::get('/', 'index')->name('.show')->withoutMiddleware('superadmin');
        Route::post('/', 'create')->name('.create');
    });

    Route::prefix('{store}')->name('store')->group(function () {
        Route::get('/', 'show')->name('.show')->whereNumber('store')->withoutMiddleware('superadmin');
        Route::put('/', 'update')->name('.update')->whereNumber('store');
        Route::delete('/', 'delete')->name('.delete')->whereNumber('store');
        Route::post('/confirm-delete', 'confirmDelete')->name('.confirm.delete')->whereNumber('store');

        Route::get('/locations', 'showLocations')->name('.locations')->whereNumber('store')->withoutMiddleware('superadmin');
        Route::post('/locations', 'createLocation')->name('.locations.create')->whereNumber('store');
    });

});

/**
 *  User Store Routes (Auth User Facing Routes)
 *
 *  The following routes require an authenticated user that accepted Terms And Conditions
 */
Route::controller(StoreController::class)
    ->middleware('accepted.terms.and.conditions')
    ->prefix('auth/user/stores')
    ->group(function () {

    Route::name('auth.user.stores')->group(function () {
        Route::get('/', 'index')->name('.show');
        Route::post('/', 'create')->name('.create');
    });

    /**
     *
    *  The following routes require the authenticated user to be a store team member
     */
    Route::prefix('{store}')
        ->middleware('assigned.to.store')
        ->name('auth.user.store')
        ->group(function () {

        Route::get('/', 'show')->name('.show')->whereNumber('store');

        //  Only creators can update or delete auth stores
        Route::withoutMiddleware('assigned.to.store')
             ->middleware('assigned.to.store:creator')
             ->group(function () {

            Route::put('/', 'update')->name('.update')->whereNumber('store');
            Route::delete('/', 'delete')->name('.delete')->whereNumber('store');
            Route::post('/confirm-delete', 'confirmDelete')->name('.confirm.delete')->whereNumber('store');

        });

        Route::get('/locations', 'showLocations')->name('.locations')->whereNumber('store');
        Route::post('/locations', 'createLocation')->name('.locations.create')->whereNumber('store');

    });

});
