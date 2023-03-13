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

Route::prefix('country')->group(function () {
    Route::get('/countries', 'CountryController@index');
});
Route::prefix('country')->group(function () {
    Route::get('/state', 'CountryController@state');
});
Route::prefix('country')->group(function () {
    Route::get('/city', 'CountryController@city');
});
Route::prefix('country')->group(function () {
    Route::get('/promotion', 'CountryController@promotion');
});
