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

Route::prefix('retailer')->group(function () {
    Route::post('/register', 'RetailerController@register');
});
Route::prefix('retailer')->group(function () {
    Route::get('/edit/{id}', 'RetailerController@edit');
});
Route::prefix('retailer')->group(function () {
    Route::post('/update-account', 'RetailerController@update');
});
