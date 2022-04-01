<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/**
 *  Product Routes (Public & Super-Admin Facing Routes)
 *
 *  Public users are allowed to view a product and its variations
 */
Route::controller(ProductController::class)
    ->middleware('superadmin')
    ->prefix('products')
    ->group(function () {

    Route::get('/', 'index')->name('products.show');

    Route::prefix('{product}')->name('product')->group(function () {
        Route::get('/', 'show')->name('.show')->whereNumber('product')->withoutMiddleware('superadmin');
        Route::put('/', 'update')->name('.update')->whereNumber('product');
        Route::delete('/', 'delete')->name('.delete')->whereNumber('product');
        Route::post('/confirm-delete', 'confirmDelete')->name('.confirm.delete')->whereNumber('product');

        Route::get('/variations', 'showVariations')->name('.variations')->whereNumber('product')->withoutMiddleware('superadmin');
    });

});

/**
 *  Product Routes
 *
 *  The following routes require an authenticated user that accepted Terms And Conditions
 *  and has been granted permissions to manage the location products
 */
Route::controller(ProductController::class)
    ->middleware(['accepted.terms.and.conditions', 'location.permission:manage products'])
    ->prefix('auth/user/products/{product}')
    ->name('auth.user.product')
    ->group(function () {

    Route::get('/', 'show')->name('.show')->whereNumber('product');
    Route::put('/', 'update')->name('.update')->whereNumber('product');
    Route::delete('/', 'delete')->name('.delete')->whereNumber('product');
    Route::post('/confirm-delete', 'confirmDelete')->name('.confirm.delete')->whereNumber('product');

    Route::get('/variations', 'showVariations')->name('.variations')->whereNumber('product');
    Route::post('/variations', 'createVariations')->name('.variations')->whereNumber('product');

});
