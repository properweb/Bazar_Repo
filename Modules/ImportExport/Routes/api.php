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
Route::prefix('importexport')->group(function () {
    Route::post('/import', 'ImportExportController@index');
});
Route::prefix('importexport')->group(function () {
    Route::get('/export', 'ImportExportController@export');
});
