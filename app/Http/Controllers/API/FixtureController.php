<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\FixtureResouce;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;

class FixtureController extends Controller
{
    public function __invoke(Request $request)
    {
        $response = Http::get('https://fantasy.premierleague.com/api/fixtures/', []);
        $data = $response->json();

        $cacheData = Cache::get('bootstrap_static_' . Carbon::now()->format('H:i'));



        if ($request->input('option') == 'confirm') {
            $confirmFixtures = Arr::where($data, function ($value, $key) {
                return $value['event'] != null;
            });
            return FixtureResouce::collection($confirmFixtures);
        }

        if ($request->input('gw')) {
            $eventFixtures = Arr::where($data, function ($value, $key) use ($request) {
                return $value['event'] == $request->input('gw');
            });
            return FixtureResouce::collection($eventFixtures);
        }
    }
}
