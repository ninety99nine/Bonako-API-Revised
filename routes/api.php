<?php

use App\Helpers\Routes\RouteHelper;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//  Api version 1 routes
Route::prefix('v1')->middleware(['auth:sanctum', 'require.api.headers'])->group(function(){

    //  Include api version 1 route files
    RouteHelper::includeRouteFiles(__DIR__ . '/api/v1/');

});
