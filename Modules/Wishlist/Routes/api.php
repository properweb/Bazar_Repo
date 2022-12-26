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

Route::prefix('wishlist')->group(function () {
    Route::get('/fetch/{id}', 'WishlistController@fetch');
});
Route::prefix('wishlist')->group(function () {
    Route::post('/add', 'WishlistController@add');
});
Route::prefix('wishlist')->group(function () {
    Route::get('/delete/{id}', 'WishlistController@delete');
});