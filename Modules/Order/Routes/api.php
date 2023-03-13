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

Route::middleware('auth:sanctum')
    ->prefix('orders')
    ->name('order.')
    ->group(function () {
        Route::get('/', 'OrderController@index')->name('index');
        Route::post('/', 'OrderController@checkout')->name('checkout');
        Route::post('details/', 'OrderController@show')->name('show');
        Route::post('/packing-slip', 'OrderController@packingSlip')->name('packingSlip');
        Route::post('/updatebilling', 'OrderController@UpdateBilling')->name('UpdateBilling');
        Route::post('/accept', 'OrderController@accept')->name('accept');
        Route::post('/change-date', 'OrderController@changeDate')->name('changeDate');
        Route::post('/change-address', 'OrderController@changeAddress')->name('changeAddress');
        Route::post('/update', 'OrderController@update')->name('update');
        Route::post('/split', 'OrderController@split')->name('split');
        Route::post('/cancel', 'OrderController@cancel')->name('cancel');
        Route::get('/csv', 'OrderController@csv')->name('csv');


    });





