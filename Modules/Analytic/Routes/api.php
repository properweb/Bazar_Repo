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
Route::prefix('analytic')
    ->name('analytic.')
    ->group(function () {
        Route::post('/shop-visit', 'AnalyticController@shopVisit')->name('shopVisit');

    });

Route::middleware('auth:api')
    ->prefix('analytic')
    ->name('analytic.')
    ->group(function () {
        Route::post('/order-cancel', 'AnalyticController@orderCancel')->name('orderCancel');
        Route::post('/top-selling', 'AnalyticController@topSelling')->name('topSelling');
        Route::post('/total-sales', 'AnalyticController@totalSales')->name('totalSales');
        Route::post('/total-sales-date', 'AnalyticController@totalSalesDates')->name('totalSalesDates');
        Route::post('/traffic', 'AnalyticController@traffic')->name('traffic');
        Route::post('/total-traffic', 'AnalyticController@totalTraffic')->name('totalTraffic');
        Route::get('/order-issues', 'AnalyticController@orderIssues')->name('order_issues');
        Route::post('/sell-through', 'AnalyticController@sellThrough')->name('sell_through');
    });
