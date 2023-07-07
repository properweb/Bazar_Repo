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

Route::middleware('auth:api')
    ->prefix('custom')
    ->name('custom.')
    ->group(function () {
        Route::post('/add-api', 'CustomController@addApi')->name('addApi');
        Route::post('/import-product', 'CustomController@importProduct')->name('importProduct');
        Route::post('/export-product', 'CustomController@exportProduct')->name('exportProduct');
        Route::post('/fetch-custom', 'CustomController@fetchCustom')->name('fetchCustom');
        Route::post('/update-stock', 'CustomController@updateStock')->name('updateStock');
    });
