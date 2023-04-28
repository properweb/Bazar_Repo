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
    ->prefix('message')
    ->name('message.')
    ->group(function () {
        Route::post('/show-member', 'MessageController@showMember')->name('showMember');
        Route::post('/chat-detail', 'MessageController@chatDetail')->name('chatDetail');
        Route::post('/create', 'MessageController@create')->name('create');
        Route::post('/all-chat', 'MessageController@allChat')->name('allChat');
    });
