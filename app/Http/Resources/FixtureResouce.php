<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class FixtureResouce extends JsonResource
{
    /**
     * Get Team Logo
     */

    public function getTeamLogo($code)
    {
        $logo_name = 't' . $code . '.png';
        $logo_small = 'https://resources.premierleague.com/premierleague/badges/50/' . $logo_name;
        $logo_large = 'https://resources.premierleague.com/premierleague/badges/100/' . $logo_name;
        $stadium_image_small = 'https://www.premierleague.com/resources/prod/f4af34b-3761/i/stadiums/club-index/t' . $code . '.jpg';
        $stadium_image_large = 'https://www.premierleague.com/resources/prod/f4af34b-3761/i/stadiums/club-profile/t' . $code . '.jpg';

        $images = [
            'logo_small' => $logo_small,
            'logo_large' => $logo_large,
            'stadium_image_small' => $stadium_image_small,
            'stadium_image_large' => $stadium_image_large,
        ];

        return $images;
    }

    public function getTeamData($code)
    {

        $cacheData = Cache::get('bootstrap_static_' . Carbon::now()->format('H:i'));

        if (!$cacheData) {
            $response = Http::get('https://fantasy.premierleague.com/api/bootstrap-static/', []);
            $data = $response->json();
            Cache::put('bootstrap_static_' . Carbon::now()->format('H:i'), $data, Carbon::now()->addMinute());
            $teams = $data['teams'];
            $teamData = Arr::where($teams, function ($value, $key) use ($code) {
                return $value['code'] == $code;
            });
        } else {
            $data = $cacheData;
            $teams = $data['teams'];
            $cacheData = Cache::get('bootstrap_static_' . Carbon::now()->format('H:i'));
            $teamData = Arr::where($teams, function ($value, $key) use ($code) {
                return $value['code'] == $code;
            });
        }

        return $teamData;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

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
            'team_a' => [
                'detail' => $this->getTeamData($this['team_a']),
                'images' =>  $this->getTeamLogo($this['team_a'])
            ],
            'team_a_score' => $this['team_a_score'],
            'team_h' => [
                'detail' => $this->getTeamData($this['team_h']),
                'images' =>  $this->getTeamLogo($this['team_h'])
            ],
            'team_h_score' => $this['team_h_score'],
            'stats' => $this['stats'],
            'team_h_difficulty' => $this['team_h_difficulty'],
            'team_a_difficulty' => $this['team_a_difficulty'],
            'pulse_id' => $this['pulse_id'],

        ];
        return $arrayData;
    }
}
