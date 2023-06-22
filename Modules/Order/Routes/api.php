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
        Route::post('/order-fulfilled', 'OrderController@orderFulfilled')->name('orderFulfilled');
        Route::post('/update-billing', 'OrderController@updateBilling')->name('retailer.update_billing');
        Route::post('/ship-from', 'OrderController@shipFrom')->name('brand.ship_from');
        Route::post('/process-order', 'OrderController@processOrder')->name('brand.process_order');
        Route::post('/change-date', 'OrderController@changeDate')->name('changeDate');
        Route::post('/change-address', 'OrderController@changeAddress')->name('changeAddress');
        Route::post('/update', 'OrderController@update')->name('brand.update');
        Route::post('/split', 'OrderController@split')->name('brand.split');
        Route::post('/cancel', 'OrderController@cancel')->name('brand.cancel');
        Route::post('/cancel-request', 'OrderController@cancelRequest')->name('retailer.cancel_request');
        Route::get('/csv', 'OrderController@csv')->name('csv');
        Route::post('/review', 'OrderController@review')->name('retailer.review');
        Route::get('/return-reasons', 'OrderController@returnReasons')->name('return_reasons');
        Route::get('/return-policies', 'OrderController@returnPolicies')->name('return_policies');
        Route::post('/return-order', 'OrderController@returnOrder')->name('retailer.return_order');
        Route::post('/process-return', 'OrderController@processReturn')->name('brand.process_return');
        Route::post('/add-payment', 'OrderController@addPayment')->name('retailer.add_payment');
    });





