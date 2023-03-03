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

Route::prefix('wordpress')->group(function () {
    Route::get('/importwordpress', 'WordpressController@index');
});
Route::prefix('wordpress')->group(function () {
    Route::get('/wordpresssync', 'WordpressController@wordpressSync');
});
Route::prefix('wordpress')->group(function () {
    Route::get('/synctowordpress', 'WordpressController@syncWordpress');
});
