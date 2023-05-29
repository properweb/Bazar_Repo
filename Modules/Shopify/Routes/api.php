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

/*
|--------------------------------------------------------------------------
| Webhook API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| For webhook API its getting from outside webhook API. So no need to check authentication here
|
*/

Route::prefix('shopify')
    ->name('shopify.')
    ->group(function () {
        Route::post('/web-hook-create', 'ShopifyController@webHookCreate')->name('webhookCreate');
        Route::post('/web-hook-update', 'ShopifyController@webHookUpdate')->name('webHookUpdate');
        Route::post('/web-hook-order', 'ShopifyController@webHookOrder')->name('webHookOrder');
    });

Route::middleware('auth:api')
    ->prefix('shopify')
    ->name('shopify.')
    ->group(function () {
        Route::post('/import-shopify', 'ShopifyController@importShopify')->name('importShopify');
        Route::post('/sync-shopify', 'ShopifyController@syncShopify')->name('syncShopify');
    });





