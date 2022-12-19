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


Route::prefix('shop')->group(function () {
    Route::get('/brand/{id}', 'ShopController@brand');
});
Route::prefix('shop')->group(function () {
    Route::get('/products', 'ShopController@products');
});
Route::prefix('shop')->group(function () {
    Route::get('/product', 'ShopController@product');
});