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

Route::prefix('brands')->group(function () {
    Route::post('/', 'BrandController@store')->name('brand.store');
});
Route::prefix('brands')->group(function () {
    Route::post('/update', 'BrandController@update')->name('brand.update');
});
Route::prefix('brands')->group(function () {
    Route::get('/{brand}', 'BrandController@show')->name('brand.show');
});
Route::middleware('auth:api')
    ->prefix('brands')
    ->name('brand.')
    ->group(function () {

        Route::put('/update/shop', 'BrandController@updateShop')->name('update.shop');
        Route::put('/update/account', 'BrandController@updateAccount')->name('update.account');
    });




