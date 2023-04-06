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

Route::prefix('shop')
    ->name('shop.')
    ->group(function () {
    Route::post('/brand-products', 'ShopController@fetchBrandProducts')->name('brand_products');
    Route::post('/category-products', 'ShopController@fetchCategoryProducts')->name('category_products');
    Route::get('/product', 'ShopController@fetchProductDetail')->name('product_detail');
});


