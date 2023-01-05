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
    Route::post('/delete', 'WishlistController@delete');
});
Route::prefix('wishlist')->group(function () {
    Route::get('/fetch-boards/{id}', 'WishlistController@fetchBoards');
});
Route::prefix('wishlist')->group(function () {
    Route::post('/add-board', 'WishlistController@addBoard');
});
Route::prefix('wishlist')->group(function () {
    Route::get('/fetch-board/{key}', 'WishlistController@fetchBoard');
});
Route::prefix('wishlist')->group(function () {
    Route::post('/update-board', 'WishlistController@updateBoard');
});
Route::prefix('wishlist')->group(function () {
    Route::post('/delete-board', 'WishlistController@deleteBoard');
});
Route::prefix('wishlist')->group(function () {
    Route::post('/change-board', 'WishlistController@changeBoard');
});