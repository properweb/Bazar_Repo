<?php

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

Route::prefix('orders')->group(function () {
    Route::get('/', 'OrderController@index');
    Route::post('/', 'OrderController@store');
    Route::get('/{order}', 'OrderController@show');
    Route::post('/packing-slip', 'OrderController@packingSlip');
    Route::post('/accept', 'OrderController@accept');
    Route::post('/change-date', 'OrderController@changeDate');
    Route::post('/change-address', 'OrderController@changeAddress');
    Route::post('/update', 'OrderController@update');
    Route::post('/split', 'OrderController@split');
    Route::post('/cancel', 'OrderController@cancel');
    Route::get('/csv', 'OrderController@csv');
});