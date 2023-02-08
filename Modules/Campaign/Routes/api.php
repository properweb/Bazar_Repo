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

Route::prefix('campaigns')->group(function () {
    Route::get('/', 'CampaignController@index');
    Route::post('/', 'CampaignController@store');
    Route::get('/{brand}/{campaign}', 'CampaignController@show');
    Route::post('/update', 'CampaignController@update');
    Route::post('/delete', 'CampaignController@destroy');
});