<?php

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
    ->prefix('carts')
    ->name('cart.')
    ->group(function () {
        Route::get('/fetch', 'CartController@fetch')->name('fetch');
        Route::post('/add', 'CartController@add')->name('add');
        Route::post('/delete', 'CartController@delete')->name('delete');
        Route::post('/update', 'CartController@update')->name('update');
    });
