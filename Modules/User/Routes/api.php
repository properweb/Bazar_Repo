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
Route::prefix('user')->group(function () {
    Route::post('/login', 'UserController@login')->name('user.login');
});
Route::prefix('user')->group(function () {
    Route::post('/forget-password', 'UserController@forgetPassword')->name('user.forgot_password');
});
Route::prefix('user')->group(function () {
    Route::post('/reset-password', 'UserController@resetPassword')->name('user.reset_password');
});


Route::middleware('auth:sanctum')
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::post('/details', 'UserController@details')->name('user.details');
        Route::get('/fetch-cart/{id}', 'UserController@fetchCart')->name('user.fetch_cart');
        Route::get('/fetch-brands/{id}', 'UserController@fetchBrands')->name('user.fetch_brands');
    });

