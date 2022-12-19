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

Route::prefix('brandproduct')->group(function () {
    Route::GET('/synclist', 'BrandProductController@index');
});
Route::prefix('brandproduct')->group(function () {
    Route::get('/fetchproductbysort', 'BrandProductController@ProductSort');
});
Route::prefix('brandproduct')->group(function () {
    Route::get('/productdetails', 'BrandProductController@productDetails');
});
Route::prefix('brandproduct')->group(function () {
    Route::get('/fetchproductbyvendor', 'BrandProductController@fetchProductByVendor');
});
Route::prefix('brandproduct')->group(function () {
    Route::GET('fetch-products', 'BrandProductController@fetchProducts');
});
