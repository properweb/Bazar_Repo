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

//Route::middleware('auth:api')->get('/brand', function (Request $request) {
//    return $request->user();
//});
Route::prefix('brand')->group(function () {
    Route::post('/register', 'BrandController@register');
});
Route::prefix('brand')->group(function () {
    Route::post('/create', 'BrandController@create');
});
Route::prefix('brand')->group(function () {
    Route::get('/edit/{id}', 'BrandController@edit');
});
Route::prefix('brand')->group(function () {
    Route::post('/update-account', 'BrandController@updateAccount');
});
Route::prefix('brand')->group(function () {
    Route::post('/update-shop', 'BrandController@updateShop');
});
Route::prefix('brand')->group(function () {
    Route::post('/golive', 'BrandController@goLive');
});
Route::prefix('brand')->group(function () {
    Route::get('/all/{retailer}', 'BrandController@all');
});
Route::prefix('brand')->group(function () {
    Route::get('/orders', 'BrandController@orders');
});
Route::prefix('brand')->group(function () {
    Route::get('/orders/csv', 'BrandController@ordersCSV');
});
Route::prefix('brand')->group(function () {
    Route::post('/orders/packingslip', 'BrandController@ordersPackingSlip');
});
Route::prefix('brand')->group(function () {
    Route::get('/order/{order}', 'BrandController@order');
});
Route::prefix('brand')->group(function () {
    Route::post('/order/changedate', 'BrandController@changeDateOrder');
});
Route::prefix('brand')->group(function () {
    Route::post('/order/changeaddress', 'BrandController@changeAddressOrder');
});
Route::prefix('brand')->group(function () {
    Route::post('/order/accept', 'BrandController@acceptOrder');
});
Route::prefix('brand')->group(function () {
    Route::post('/order/update', 'BrandController@updateOrder');
});
Route::prefix('brand')->group(function () {
    Route::post('/order/split', 'BrandController@splitOrder');
});
Route::prefix('brand')->group(function () {
    Route::post('/order/cancel', 'BrandController@cancelOrder');
});



//Route::middleware(['cors'])->group(function () {
//    Route::get('/orders', 'BrandController@fetchOrders');
//});
//Route::middleware(['cors'])->group(function () {
//    Route::get('/order/{order}', 'BrandController@fetchOrderDetails');
//});
//Route::middleware(['cors'])->group(function () {
//    Route::post('/order-multiple', 'BrandController@fetchMultipleOrderDetails');
//});
//Route::middleware(['cors'])->group(function () {
//    Route::post('/brand-ship-from', 'BrandController@orderShippingDetails');
//});
//Route::middleware(['cors'])->group(function () {
//    Route::post('/edit-ship-order', 'BrandController@editShipOrder');
//});
//Route::middleware(['cors'])->group(function () {
//    Route::post('/update-order', 'BrandController@updateOrder');
//});
//Route::middleware(['cors'])->group(function () {
//    Route::post('/cancel-order', 'BrandController@cancelOrder');
//});
//Route::middleware(['cors'])->group(function () {
//    Route::post('/split-order', 'BrandController@splitOrder');
//});
