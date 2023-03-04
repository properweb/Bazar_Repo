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

Route::middleware('auth:api')
    ->prefix('product')
    ->name('product.')
    ->group(function () {
        Route::get('/fetch', 'ProductController@fetch')->name('fetch');
        Route::get('/arrange', 'ProductController@arrangeProduct')->name('arrangeProduct');
        Route::get('/fetch-stock', 'ProductController@fetchStock')->name('fetchStock');
        Route::get('/details', 'ProductController@details')->name('details');
        Route::post('/create', 'ProductController@create')->name('create');
        Route::post('/update', 'ProductController@update')->name('update');
        Route::post('/status', 'ProductController@status')->name('status');
        Route::post('/delete', 'ProductController@delete')->name('delete');
        Route::post('/delete-image', 'ProductController@deleteImage')->name('deleteImage');
        Route::post('/delete-video', 'ProductController@deleteVideo')->name('deleteVideo');
        Route::post('/reorder', 'ProductController@reorderProduct')->name('reorderProduct');
        Route::post('/update-stock', 'ProductController@updateStock')->name('updateStock');
        Route::get('/convert-price/{price}', 'ProductController@convertPrice')->name('convertPrice');
    });



