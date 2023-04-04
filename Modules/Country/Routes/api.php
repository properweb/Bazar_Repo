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

Route::prefix('country')
    ->name('country.')
    ->group(function () {
        Route::get('/countries', 'CountryController@index')->name('country');
        Route::get('/state', 'CountryController@state')->name('state');
        Route::get('/city', 'CountryController@city')->name('city');
        Route::get('/promotion', 'CountryController@promotion')->name('promotion');
    });


