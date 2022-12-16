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

Route::prefix('cart')->group(function () {
    Route::get('/fetch/{id}', 'CartController@fetch');
});
Route::prefix('cart')->group(function () {
    Route::post('/add', 'CartController@add');
});
Route::prefix('cart')->group(function () {
    Route::get('/delete/{id}', 'CartController@delete');
});
Route::prefix('cart')->group(function () {
    Route::post('/update', 'CartController@update');
});
