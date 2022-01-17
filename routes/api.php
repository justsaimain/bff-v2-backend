<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'API'], function () {
    Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
        Route::post('register', 'RegisterController');
        Route::post('verify', 'VerifyController');
        Route::post('get-otp', 'GetOTPController');
        Route::post('login', 'LoginController');
        Route::get('me', 'MeController');
        Route::post('logout', 'LogoutController');
    });
});
