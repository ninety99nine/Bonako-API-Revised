<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\Auth\AuthController;

/**
 *  Auth Routes
 */
Route::controller(AuthController::class)->prefix('auth')->name('auth')->group(function () {

    //  The following routes require an authenticated user
    Route::prefix('user')->name('.user')->group(function(){

        Route::name('.profile')->group(function() {

            Route::get('/', 'showProfile')->name('.show');
            Route::put('/', 'updateProfile')->name('.update');
            Route::delete('/', 'deleteProfile')->name('.delete');
            Route::post('/confirm-delete', 'confirmDeleteProfile')->name('.confirm.delete');

        });

        Route::post('/logout', 'logout')->name('.logout');
        Route::get('/tokens', 'showProfileTokens')->name('.tokens');
        Route::post('/accept-terms-and-conditions', 'acceptTermsAndConditions')->name('.accept.terms.and.conditions');

    });

    //  The following routes do not require an authenticated user
    Route::withoutMiddleware('auth:sanctum')->group(function () {

        Route::post('/login', 'login')->name('.login');
        Route::post('/register', 'register')->name('.register');
        Route::post('/account-exists', 'accountExists')->name('.account.exists');
        Route::post('/reset-password', 'resetPassword')->name('.reset.password');
        Route::post('/verify-mobile-verification-code', 'verifyMobileVerificationCode')->name('.verify.mobile.verification.code');
        Route::post('/generate-mobile-verification-code', 'generateMobileVerificationCode')->name('.generate.mobile.verification.code');

        //  The following route is only authourized for the USSD server (Check attached middleware)
        Route::post('/show-mobile-verification-code', 'showMobileVerificationCode')->name('.show.mobile.verification.code')->middleware('request.via.ussd');

    });

});
