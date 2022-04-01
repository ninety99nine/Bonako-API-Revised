<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/**
 *  User Routes (Public & Super-Admin Facing Routes)
 */
Route::controller(UserController::class)
    ->prefix('users')
    ->group(function () {

    Route::name('users')->group(function () {
        Route::get('/', 'index')->name('.show')->middleware('superadmin');
        Route::post('/', 'createProfile')->name('.create')->middleware('superadmin');
    });

    Route::middleware('superadmin')
        ->prefix('{user}')
        ->name('user')
        ->group(function () {

        Route::name('.profile')->group(function () {

            //  Enable public access so that team members can see other team member profiles
            Route::get('/', 'showProfile')->name('.show')->withoutMiddleware('superadmin')->whereNumber('user');

            Route::put('/', 'updateProfile')->name('.update')->whereNumber('user');
            Route::delete('/', 'deleteProfile')->name('.delete')->whereNumber('user');
            Route::post('/confirm-delete', 'confirmDeleteProfile')->name('.confirm.delete');

        });

        Route::post('/logout', 'logout')->name('.logout');
        Route::get('/tokens', 'showProfileTokens')->name('.tokens');
        Route::post('/accept-terms-and-conditions', 'acceptTermsAndConditions')->name('.accept.terms.and.conditions');
        Route::post('/show-mobile-verification-code', 'showMobileVerificationCode')->name('.show.mobile.verification.code');
        Route::post('/verify-mobile-verification-code', 'verifyMobileVerificationCode')->name('.verify.mobile.verification.code');
        Route::post('/generate-mobile-verification-code', 'generateMobileVerificationCode')->name('.generate.mobile.verification.code');

    });

});

/**
 *  Authenticated User Routes (refer to the auth.php file)
 */
