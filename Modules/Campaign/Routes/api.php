<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Modules\Campaign\Http\Controllers\CampaignController;
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
    Route::prefix('campaigns')->group(function () {
        Route::get('/', 'CampaignController@index');
        Route::post('/', 'CampaignController@store');
        Route::get('/{campaign}', 'CampaignController@show');
        Route::put('/update', 'CampaignController@update');
        Route::delete('/delete/{campaign}', 'CampaignController@destroy');
    });
});





