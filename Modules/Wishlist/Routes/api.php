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
    ->prefix('wishlist')
    ->name('wishlist.')
    ->group(function () {
        Route::get('/fetch', 'WishlistController@fetch')->name('fetch');
        Route::post('/add', 'WishlistController@add')->name('add');
        Route::post('/delete', 'WishlistController@delete')->name('delete');
        Route::get('/fetch-boards', 'WishlistController@fetchBoards')->name('fetchBoards');
        Route::post('/add-board', 'WishlistController@addBoard')->name('addBoard');
        Route::get('/fetch-board/{key}', 'WishlistController@fetchBoard')->name('fetchBoard');
        Route::post('/update-board', 'WishlistController@updateBoard')->name('updateBoard');
        Route::post('/delete-board', 'WishlistController@deleteBoard')->name('deleteBoard');
        Route::post('/change-board', 'WishlistController@changeBoard')->name('changeBoard');
    });
