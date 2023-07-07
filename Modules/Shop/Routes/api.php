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
Route::middleware('auth:api')
    ->prefix('shop')
    ->name('shop.')
    ->group(function () {
        Route::get('/product', 'ShopController@fetchProductDetail')->name('product_detail');
    });

Route::prefix('shop')
    ->name('shop.')
    ->group(function () {
        Route::post('/brand-products', 'ShopController@fetchBrandProducts')->name('brand_products');
        Route::post('/category-products', 'ShopController@fetchCategoryProducts')->name('category_products');
        Route::post('/product-filters', 'ShopController@fetchProductFilters')->name('product_filters');
        Route::get('/new-brands', 'ShopController@fetchNewBrands')->name('new_brands');
        Route::get('/testimonials', 'ShopController@fetchTestimonials')->name('testimonials');
        Route::get('/trending-categories', 'ShopController@fetchTrendingCategories')->name('trending_categories');
        Route::get('/brand-reviews', 'ShopController@fetchBrandReviews')->name('brand_reviews');
        Route::get('/search', 'ShopController@search')->name('search');
        Route::get('/search-result', 'ShopController@searchResult')->name('searchResult');
    });


