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
    Route::get('/countries', 'ProductController@countries');
});
Route::prefix('product')->group(function () {
    Route::get('/allcategory', 'ProductController@category');
});
Route::prefix('product')->group(function () {
    Route::get('/convertprice/{price}', 'ProductController@convertPrice');
});
Route::prefix('product')->group(function () {
    Route::get('/fetchproductbysort', 'ProductController@fetchproductbysort');
});
Route::prefix('product')->group(function () {
    Route::get('/productdetails', 'ProductController@productDetails');
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
    Route::get('/fetchproductbyvendor', 'ProductController@fetchProductByVendor');
});
Route::prefix('product')->group(function () {
    Route::post('/productsreorder', 'ProductController@productsReorder');
});
Route::prefix('product')->group(function () {
    Route::GET('importshopify', 'ProductController@importShopify');
});
Route::prefix('product')->group(function () {
    Route::GET('/importwordpress', 'ProductController@importWordpress');
});
Route::prefix('product')->group(function () {
    Route::GET('fetch-products', 'ProductController@fetchProducts');
});
Route::prefix('product')->group(function () {
    Route::post('/update-products-stock', 'ProductController@updateProductsStock');
});

Route::prefix('product')->group(function () {
    Route::GET('/synclist', 'ProductController@syncList');
});

