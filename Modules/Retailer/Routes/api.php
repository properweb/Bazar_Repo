<?php

use Illuminate\Http\Request;
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
Route::prefix('retailers')->group(function () {
Route::get('/resetpassword', 'RetailerController@resetPassword')->name('retailer.reset_password');
});
Route::prefix('retailers')->group(function () {
    Route::post('/', 'RetailerController@store')->name('store');
});
Route::middleware('auth:api')
    ->prefix('retailers')
    ->name('retailer.')
    ->group(function () {
        Route::get('/{retailer}', 'RetailerController@show')->name('show');
        Route::put('/{retailer}', 'RetailerController@update')->name('update');
    });
