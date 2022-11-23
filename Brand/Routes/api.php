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
    Route::post('/update', 'BrandController@update');
});
Route::prefix('brand')->group(function () {
    Route::get('/parentcategory', 'BrandController@category');
});