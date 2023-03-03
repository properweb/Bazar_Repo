<?php

use Illuminate\Support\Facades\Route;

use Modules\Wishlist\Http\Controllers\WishlistController;
use Modules\User\Http\Controllers\UserController;

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

Route::middleware('auth:sanctum')
    ->prefix('wishlist')
    ->name('wishlist.')
    ->group(function () {
        Route::get('/fetch/{id}', 'WishlistController@fetch')->name('fetch');
        Route::post('/add', 'WishlistController@add')->name('add');
        Route::post('/delete', 'WishlistController@delete')->name('delete');
        Route::get('/fetch-boards/{id}', 'WishlistController@fetchBoards')->name('fetchBoards');
        Route::post('/add-board', 'WishlistController@addBoard')->name('addBoard');
        Route::get('/fetch-board/{key}', 'WishlistController@fetchBoard')->name('fetchBoard');
        Route::post('/update-board', 'WishlistController@updateBoard')->name('updateBoard');
        Route::post('/delete-board', 'WishlistController@deleteBoard')->name('deleteBoard');
        Route::post('/change-board', 'WishlistController@changeBoard')->name('changeBoard');
    });
