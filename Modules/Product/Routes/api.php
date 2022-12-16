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

Route::prefix('product')->group(function () {
    Route::get('/', 'ProductController@index');
});
Route::prefix('product')->group(function () {
    Route::post('/create', 'ProductController@create');
});

Route::prefix('product')->group(function () {
    Route::get('/convertprice/{price}', 'ProductController@convertPrice');
});

Route::prefix('product')->group(function () {
    Route::post('/updateproduct', 'ProductController@update');
});
Route::prefix('product')->group(function () {
    Route::get('/statusproduct', 'ProductController@changeStatus');
});
Route::prefix('product')->group(function () {
    Route::get('/deleteproduct', 'ProductController@delete');
});
Route::prefix('product')->group(function () {
    Route::get('/deleteproductimage', 'ProductController@deleteProductImage');
});
Route::prefix('product')->group(function () {
    Route::get('/deleteproductvideo', 'ProductController@deleteProductVideo');
});

Route::prefix('product')->group(function () {
    Route::post('/productsreorder', 'ProductController@productsReorder');
});

Route::prefix('product')->group(function () {
    Route::post('/update-products-stock', 'ProductController@updateProductsStock');
});


