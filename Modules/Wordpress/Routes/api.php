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
Route::prefix('wordpress')
    ->name('wordpress.')
    ->group(function () {
        Route::post('/web-hook-update', 'WordpressController@webHookUpdate')->name('webhookUpdate');
        Route::post('/web-hook-create', 'WordpressController@webHookCreate')->name('webhookCreate');
        Route::post('/web-hook-delete', 'WordpressController@webHookDelete')->name('webhookDelete');
    });

Route::middleware('auth:api')
    ->prefix('wordpress')
    ->name('wordpress.')
    ->group(function () {
        Route::post('/', 'WordpressController@index')->name('create');
        Route::get('/action-info', 'WordpressController@actionInfo')->name('actionInfo');
        Route::post('/create-product', 'WordpressController@createProduct')->name('createProduct');
    });





