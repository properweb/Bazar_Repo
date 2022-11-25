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
    Route::get('/allcategory', 'ProductController@allcategory');
});
Route::prefix('product')->group(function () {
    Route::get('/convertprice/{price}', 'ProductController@convertprice');
});
Route::prefix('product')->group(function () {
    Route::get('/fetchproductbysort', 'ProductController@fetchproductbysort');
});
Route::prefix('product')->group(function () {
    Route::get('/productdetails', 'ProductController@productdetails');
});
Route::prefix('product')->group(function () {
    Route::post('/updateproduct', 'ProductController@updateproduct');
});
Route::prefix('product')->group(function () {
    Route::get('/statusproduct', 'ProductController@statusproduct');
});
Route::prefix('product')->group(function () {
    Route::get('/deleteproduct', 'ProductController@deleteproduct');
});
Route::prefix('product')->group(function () {
    Route::get('/deleteproductimage', 'ProductController@deleteproductimage');
});
Route::prefix('product')->group(function () {
    Route::get('/deleteproductvideo', 'ProductController@deleteproductvideo');
});
Route::prefix('product')->group(function () {
    Route::get('/fetchproductbyvendor', 'ProductController@fetchproductbyvendor');
});
Route::prefix('product')->group(function () {
    Route::post('/productsreorder', 'ProductController@productsreorder');
});
Route::prefix('product')->group(function () {
    Route::GET('importshopify', 'ProductController@ImportShopify');
});
Route::prefix('product')->group(function () {
    Route::GET('/importwordpress', 'ProductController@Importwordpress');
});
Route::prefix('product')->group(function () {
    Route::GET('fetch-products', 'ProductController@FetchProducts');
});
Route::prefix('product')->group(function () {
    Route::post('/update-products-stock', 'ProductController@UpdateProductsStock');
});
Route::prefix('product')->group(function () {
    Route::GET('/synctoshopify', 'ProductController@SyncToShopify');
});
