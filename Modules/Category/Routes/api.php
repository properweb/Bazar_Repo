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
Route::prefix('category')
    ->name('category.')
    ->group(function () {
        Route::get('/categories', 'CategoryController@fetchCategories')->name('all');
        Route::get('/featured-categories', 'CategoryController@fetchFeaturedCategories')->name('featured');
        Route::get('/product-categories', 'CategoryController@fetchProductCategories')->name('product');
        Route::get('/parent-categories', 'CategoryController@fetchParentCategories')->name('parent');
    });
