<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;

/**
 *  Store Routes
 */
Route::controller(StoreController::class)->prefix('stores')->name('stores')->group(function () {

    Route::get('/', 'index');
    Route::post('/', 'create')->name('.create');

    Route::prefix('{store}')->group(function () {
        Route::get('/', 'show')->name('.show')->whereNumber('store');
        Route::put('/', 'update')->name('.update')->whereNumber('store');
        Route::delete('/', 'delete')->name('.delete')->whereNumber('store');
    });

});

/**
 *  User Store Routes
 *
 *  The following routes require an authenticated user that accepted Terms And Conditions
 */
Route::controller(StoreController::class)->middleware('accepted.terms.and.conditions')->prefix('auth/user/stores')->name('auth.user.stores')->group(function () {

    Route::get('/', 'index');
    Route::post('/', 'create')->name('.create');

    Route::prefix('{store}')->group(function () {
        Route::get('/', 'show')->name('.show')->whereNumber('store');
        Route::put('/', 'update')->name('.update')->whereNumber('store');
        Route::delete('/', 'delete')->name('.delete')->whereNumber('store');
    });

});
