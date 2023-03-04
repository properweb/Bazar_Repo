<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Modules\Shipping\Http\Controllers\ShippingController;
use Modules\User\Http\Controllers\UserController;

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

Route::middleware('auth:sanctum')
    ->prefix('shippings')
    ->name('shipping.')
    ->group(function () {
        Route::get('/fetch', 'ShippingController@fetch')->name('fetch');
        Route::get('/details', 'ShippingController@details')->name('details');
        Route::post('/create', 'ShippingController@create')->name('create');
        Route::post('/update', 'ShippingController@update')->name('update');
        Route::post('/delete', 'ShippingController@delete')->name('delete');

    });





