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
Route::prefix('frontend')->group(function () {
    Route::get('/countries', 'FrontendController@countries');
});
Route::prefix('frontend')->group(function () {
    Route::get('/states/{country}', 'FrontendController@states');
});
Route::prefix('frontend')->group(function () {
    Route::get('/cities/{state}', 'FrontendController@cities');
});
Route::prefix('frontend')->group(function () {
    Route::post('/login', 'FrontendController@login');
});
Route::prefix('frontend')->group(function () {
    Route::get('/brand/edit/{id}', 'FrontendController@brandEdit');
});
Route::prefix('frontend')->group(function () {
    Route::post('/brand/update', 'FrontendController@brandUpdate');
});
Route::prefix('frontend')->group(function () {
    Route::post('/forget-password', 'FrontendController@forgetPassword');
});
Route::prefix('frontend')->group(function () {
    Route::post('/reset-password', 'FrontendController@resetPassword');
});
//Route::prefix('frontend')->group(function () {
//    Route::post('/update', 'FrontendController@update');
//});
Route::prefix('frontend')->group(function () {
    Route::get('/fetch-cart/{id}', 'FrontendController@fetchCart');
});
Route::prefix('frontend')->group(function () {
    Route::get('/fetch-brands/{id}', 'FrontendController@fetchBrands');
});
