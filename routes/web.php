<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;


Route::get('login', 'Auth\LoginController@showLoginForm');
Route::post('login', 'Auth\LoginController@login')->name('login');
Route::post('logout', 'Auth\LogoutController')->name('logout');

Route::get('/test', function () {
    $response =  Http::withHeaders([
        'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
        'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
    ])->get('https://fantasy-premier-league3.p.rapidapi.com/fixtures');
    return $response->json();
});


Route::middleware('auth:admin')->group(function () {

    Route::get('/ssd/users', 'DataTableController@getUsers');
    Route::get('/ssd/fixtures', 'DataTableController@getFixtures');

    Route::get('/', 'DashboardController@index')->name('dashboard');

    Route::get('/settings', 'SettingController@index')->name('settings.index');


    Route::get('/teams', 'TeamController@index')->name('teams.index');

    Route::get('/fixtures', 'FixtureController@index')->name('fixtures.index');

    Route::get('/predictions', 'PredictionController@index')->name('predictions.index');

    Route::get('users', 'UserController@index')->name('users.index');
    Route::get('users/{id}', 'UserController@show')->name('users.show');
});
