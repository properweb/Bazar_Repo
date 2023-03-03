<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('backend')->group(function() {
    Route::get('/', 'BackendController@index');
});
Route::prefix('backend')->group(function() {
    Route::post('/', 'BackendController@checkLogin');
});
Route::prefix('backend')->group(function() {
    Route::get('/logout', 'BackendController@logOut');
});
Route::prefix('backend')->group(function() {
    Route::get('/changepassword', 'BackendController@changePassword');
});
Route::prefix('backend')->group(function() {
    Route::post('/changepassword', 'BackendController@updatePassword');
});
Route::prefix('backend')->group(function() {
    Route::get('/vendorlist', 'VendorController@index');
});
Route::prefix('backend')->group(function() {
    Route::get('/vendoralllist', 'VendorController@vendorList');
});
Route::prefix('backend')->group(function() {
    Route::get('/vendordetails', 'VendorController@vendorDetails');
});
Route::prefix('backend')->group(function() {
    Route::get('/dashboard', 'DashboardController@index');
});
Route::prefix('backend')->group(function() {
    Route::get('/product', 'ProductController@index');
});
Route::prefix('backend')->group(function() {
    Route::get('/vendorproduct', 'ProductController@vendorProduct');
});
Route::prefix('backend')->group(function() {
    Route::get('/productdetail', 'ProductController@productDetail');
});
