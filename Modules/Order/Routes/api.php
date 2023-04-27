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

Route::middleware('auth:api')
    ->prefix('orders')
    ->name('order.')
    ->group(function () {
        Route::get('/', 'OrderController@index')->name('index');
        Route::post('/', 'OrderController@checkout')->name('checkout');
        Route::get('/details/{order}', 'OrderController@show')->name('show');
        Route::post('/packing-slip', 'OrderController@packingSlip')->name('packingSlip');
        Route::post('/update-billing', 'OrderController@updateBilling')->name('updateBilling');
        Route::post('/accept', 'OrderController@accept')->name('accept');
        Route::post('/change-date', 'OrderController@changeDate')->name('changeDate');
        Route::post('/change-address', 'OrderController@changeAddress')->name('changeAddress');
        Route::post('/update', 'OrderController@update')->name('update');
        Route::post('/split', 'OrderController@split')->name('split');
        Route::post('/cancel', 'OrderController@cancel')->name('cancel');
        Route::post('/cancel-request', 'OrderController@cancelRequest')->name('cancel_request');
        Route::get('/csv', 'OrderController@csv')->name('csv');
        Route::post('/review', 'OrderController@review')->name('review');
        Route::delete('/review/delete/{id}','OrderController@reviewDelete')->name('review_delete');
        Route::get('/review/edit/{id}','OrderController@reviewEdit')->name('review_edit');
        Route::patch('/review/update/{id}','OrderController@reviewUpdate')->name('review_update');
        Route::get('/return-reasons', 'OrderController@returnReasons')->name('return_reasons');
        Route::get('/return-policies', 'OrderController@returnPolicies')->name('return_policies');
        Route::post('/return-order', 'OrderController@returnOrder')->name('return_order');

    });





