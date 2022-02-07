<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TeamController extends Controller
{
    public function __invoke()
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

        $arrayData = [];
        foreach ($data as $value) {
            $team = [];
            $team['text'] = $value['name'];
            $team['value'] = $value['code'];
            array_push($arrayData, $team);
        }

        return response()->json($arrayData, 200);
    }
}
