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
        Route::get('/', 'CustomerController@sortedList')->name('sorted_list');
        Route::get('/all', 'CustomerController@allList')->name('all_list');
        Route::post('/', 'CustomerController@store')->name('store');
        Route::post('/create', 'CustomerController@create')->name('create');
        Route::get('/{customer}', 'CustomerController@show')->name('show');
        Route::post('/update', 'CustomerController@update')->name('update');
        Route::post('/update-info', 'CustomerController@updateContactInfo')->name('update_contact_info');
        Route::post('/update-shipping', 'CustomerController@updateShippingDetails')->name('update_shipping_details');
        Route::post('/delete', 'CustomerController@destroy')->name('delete');
        Route::post('/import', 'CustomerController@import')->name('import');
        Route::post('/export', 'CustomerController@export')->name('export');
    });
