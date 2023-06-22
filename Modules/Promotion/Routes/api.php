<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Modules\Promotion\Http\Controllers\PromotionController;
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

Route::middleware('auth:api')
    ->prefix('promotions')
    ->name('promotion.')
    ->group(function () {
        Route::get('/features', 'PromotionController@featuresList')->name('features');
        Route::get('/', 'PromotionController@index')->name('list');
        Route::post('/', 'PromotionController@store')->name('store');
        Route::get('/{promotion}', 'PromotionController@show')->name('show');
        Route::post('/update', 'PromotionController@update')->name('update');
        Route::delete('/delete/{promotion}', 'PromotionController@destroy')->name('delete');
    });
