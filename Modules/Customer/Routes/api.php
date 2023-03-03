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

Route::prefix('customers')->group(function () {
    Route::get('/', 'CustomerController@index');
    Route::post('/', 'CustomerController@store');
    Route::get('/{customer}', 'CustomerController@show');
    Route::post('/update', 'CustomerController@update');
    Route::post('/import', 'CustomerController@import');
    Route::post('/delete', 'CustomerController@destroy');
    Route::post('/export', 'CustomerController@export');
});