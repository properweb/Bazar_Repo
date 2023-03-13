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

Route::middleware('auth:api')
    ->prefix('campaigns')
    ->name('campaign.')
    ->group(function () {
        Route::get('/', 'CampaignController@index')->name('list');
        Route::post('/', 'CampaignController@store')->name('store');
        Route::get('/{campaign}', 'CampaignController@show')->name('show');
        Route::put('/update', 'CampaignController@update')->name('update');
        Route::delete('/delete/{campaign}', 'CampaignController@destroy')->name('delete');
    });





