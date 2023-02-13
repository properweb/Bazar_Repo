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

Route::prefix('promotions')->name('promotions.')->group(function () {
    Route::get('/', 'PromotionController@index');
    Route::post('/', 'PromotionController@store');
    Route::get('/{brand}/{promotion}', 'PromotionController@show');
    Route::post('/update', 'PromotionController@update');
    Route::post('/delete', 'PromotionController@destroy');
});
