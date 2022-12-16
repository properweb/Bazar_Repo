<?php

use Illuminate\Http\Request;

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
    Route::post('/login', 'UserController@login');
});
Route::prefix('user')->group(function () {
    Route::post('/forget-password', 'UserController@forgetPassword');
});
Route::prefix('user')->group(function () {
    Route::post('/reset-password', 'UserController@resetPassword');
});
//Route::prefix('user')->group(function () {
//    Route::post('/update', 'UserController@update');
//});
Route::prefix('user')->group(function () {
    Route::get('/fetch-cart/{id}', 'UserController@fetchCart');
});
Route::prefix('user')->group(function () {
    Route::get('/fetch-brands/{id}', 'UserController@fetchBrands');
});
