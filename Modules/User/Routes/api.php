<?php

use Illuminate\Http\Request;
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

Route::prefix('user')
    ->name('user.')
    ->group(function () {
        Route::post('/login', 'UserController@login')->name('login');
        Route::post('/forget-password', 'UserController@forgetPassword')->name('forgot_password');
        Route::post('/reset-password', 'UserController@resetPassword')->name('reset_password');
        Route::post('/check-email', 'UserController@checkEmail')->name('check_email');
    });

