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

Route::prefix('category')->group(function () {
    Route::get('/allcategory', 'CategoryController@index');
});
Route::prefix('category')->group(function () {
    Route::get('/parentcategory', 'CategoryController@parentCategory');
});
