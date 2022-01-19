<?php

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

Route::get('/test', function () {
    $code = 1;
    $cacheData = Cache::get('bootstrap_static_' . '_data');
    if (!$cacheData) {
        $response = Http::get('https://fantasy.premierleague.com/api/bootstrap-static/', []);
        $data = $response->json();
        Cache::put('bootstrap_static_' . '_data', $data,  Carbon::now()->addSeconds(8));
        $teams = $data['teams'];
        $teamData = Arr::where($teams, function ($value, $key) use ($code) {
            return $value['code'] == $code;
        });
        return $teamData;
    } else {
        return 'cache data';
    }
});
