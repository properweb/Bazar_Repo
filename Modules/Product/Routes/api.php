<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Route
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
        Route::get('/', 'ProductController@index');
        Route::get('/fetch', 'ProductController@fetch')->name('fetch');
        Route::get('/arrange', 'ProductController@arrange')->name('arrange');
        Route::get('/fetch-stock', 'ProductController@FetchStock')->name('FetchStock');
        Route::get('/details', 'ProductController@details')->name('details');
        Route::post('/create', 'ProductController@create')->name('create');
        Route::post('/update', 'ProductController@update')->name('update');
        Route::post('/status', 'ProductController@status')->name('status');
        Route::post('/delete', 'ProductController@delete')->name('delete');
        Route::post('/delete-image', 'ProductController@DeleteImage')->name('DeleteImage');
        Route::post('/delete-video', 'ProductController@DeleteVideo')->name('DeleteVideo');
        Route::post('/reorder', 'ProductController@reorder')->name('reorder');
        Route::post('/update-stock', 'ProductController@UpdateStock')->name('UpdateStock');
        Route::get('/convert-price/{price}', 'ProductController@ConvertPrice')->name('convertPrice');
    });



