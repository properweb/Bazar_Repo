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

Route::prefix('brand')->group(function () {
    Route::post('/register', 'BrandController@register');
});
Route::prefix('brand')->group(function () {
    Route::post('/create', 'BrandController@create');
});
Route::prefix('brand')->group(function () {
    Route::get('/edit/{id}', 'BrandController@edit');
});
Route::prefix('brand')->group(function () {
    Route::post('/update-account', 'BrandController@updateAccount');
});
Route::prefix('brand')->group(function () {
    Route::post('/update-shop', 'BrandController@updateShop');
});
Route::prefix('brand')->group(function () {
    Route::post('/golive', 'BrandController@goLive');
});
Route::prefix('brand')->group(function () {
    Route::get('/all/{retailer}', 'BrandController@all');
});
Route::prefix('brand')->group(function () {
    Route::get('/customers', 'BrandController@customers');
});
Route::prefix('brand')->group(function () {
    Route::post('/add-customer', 'BrandController@addCustomer');
});

