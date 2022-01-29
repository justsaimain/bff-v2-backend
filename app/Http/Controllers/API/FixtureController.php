<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\FixtureResource;

class FixtureController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->getTeamsData();

        $fixtures = Http::withHeaders([
            'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
            'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
        ])->get(config('url.fixtures'), [
            'gw' => $request->input('gw'),
            'h' => $request->input('h'),
            'a' => $request->input('a'),
            'hs' => $request->input('hs'),
            'as' => $request->input('as'),
        ])->json();

        return FixtureResource::collection($fixtures);
    }

    public function getTeamsData()
    {

        $cacheData = Cache::get('teams__data__' . Carbon::now()->format('H:i'));

        if (!$cacheData) {
            $teams = Http::withHeaders([
                'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
                'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
            ])->get('https://fantasy-premier-league3.p.rapidapi.com/teams/simple', [])->json();

            Cache::put('teams__data__' . Carbon::now()->format('H:i'), $teams, Carbon::now()->addMinute());
            $data = $teams;
        } else {
            $data = $cacheData;
        }

        return $data;
    }
}
