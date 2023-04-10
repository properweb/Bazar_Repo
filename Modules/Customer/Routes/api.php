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

Route::middleware('auth:api')
    ->prefix('customers')
    ->name('customer.')
    ->group(function () {
        Route::get('/', 'CustomerController@index')->name('list');
        Route::post('/', 'CustomerController@store')->name('store');
        Route::get('/{customer}', 'CustomerController@show')->name('show');
        Route::post('/update', 'CustomerController@update')->name('update');
        Route::post('/delete', 'CustomerController@destroy')->name('delete');
        Route::post('/import', 'CustomerController@import')->name('import');
        Route::post('/export', 'CustomerController@export')->name('export');
    });
