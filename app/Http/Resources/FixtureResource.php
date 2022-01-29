<?php

namespace App\Http\Resources;

use App\Models\Prediction;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Resources\Json\JsonResource;

class FixtureResource extends JsonResource
{

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

    public function getTeam($id)
    {
        $teamsData = $this->getTeamsData();
        $found_key = array_search($id, array_column($teamsData, 'id'));
        return $teamsData[$found_key];
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        if (Auth::guard('api')->check()) {
            $prediction = Prediction::where('user_id', Auth::guard('api')->id())
                ->where('fixture_id', $this['id'])
                ->where('fixture_event', $this['event'])
                ->first();
        } else {
            $prediction = null;
        }

        $arrayData = [
            'code' => $this['code'],
            'event' => $this['event'],
            'finished' => $this['finished'],
            'finished_provisional' => $this['finished_provisional'],
            'id' => $this['id'],
            'kickoff_time' => $this['kickoff_time'],
            'minutes' => $this['minutes'],
            'provisional_start_time' => $this['provisional_start_time'],
            'started' => $this['started'],
            'team_a' => $this->getTeam($this['team_a'], 'team_a'),
            'team_a_score' => $this['team_a_score'],
            'team_h' => $this->getTeam($this['team_h'], 'team_h'),
            'team_h_score' => $this['team_h_score'],
            'prediction' => new PredictionResource($prediction)
        ];

        return $arrayData;
    }
}
