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

Route::prefix('brands')
    ->name('brand.')
    ->group(function () {
        Route::get('/', 'BrandController@index')->name('list');
        Route::post('/', 'BrandController@store')->name('store');
        Route::get('/count', 'BrandController@count')->name('count');
        Route::get('/{brand}', 'BrandController@show')->name('show');
        Route::put('/update', 'BrandController@update')->name('update');
        Route::get('/shop/{brand}', 'BrandController@showShop')->name('show_shop');
    });
Route::middleware('auth:api')
    ->prefix('brands')
    ->name('brand.')
    ->group(function () {
        Route::put('/update/info', 'BrandController@updateInfo')->name('update_info');
        Route::put('/update/shop', 'BrandController@updateShop')->name('update_shop');
        Route::put('/update/account', 'BrandController@updateAccount')->name('update_account');
        Route::put('/shop/live', 'BrandController@liveShop')->name('live_shop');
    });



