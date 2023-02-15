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
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('promotions')->group(function () {
        Route::get('/', 'PromotionController@index');
        Route::post('/', 'PromotionController@store');
        Route::get('/{promotion}', 'PromotionController@show');
        Route::post('/update', 'PromotionController@update');
        Route::delete('/delete/{promotion}', 'PromotionController@destroy');
    });
});
