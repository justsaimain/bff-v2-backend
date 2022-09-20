<?php

namespace App\Http\Resources;

use App\Models\Option;
use App\Models\Prediction;
use Carbon\Carbon;
use DateTime;
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
        $total_pts = 0;
        $prediction = null;
        $used_2x_booster = false;

        $breakdown = [];
        $options = Option::first();


        if (Auth::guard('api')->check()) {
            $prediction = Prediction::where('user_id', Auth::guard('api')->id())
                ->where('fixture_id', $this['id'])
                ->where('fixture_event', $this['event'])
                ->first();
            $pts_breakdown_outcome = 0;
            $pts_breakdown_goal_diff = 0;
            $pts_breakdown_home_goal = 0;
            $pts_breakdown_away_goal = 0;
            $pts_breakdown_boost = 0;

            if ($prediction) {
                if ($this['finished'] == true) {
                    $home_team_predict = $prediction->team_h_goal['value'];
                    $away_team_predict = $prediction->team_a_goal['value'];
                    $home_team_score = $this['team_h_score'];
                    $away_team_score = $this['team_a_score'];

                    // calculate win lose draw point > +3 pts

                    $home_team_win = $home_team_score > $away_team_score;
                    $home_team_win = $home_team_score > $away_team_score;
                    $both_team_draw = $home_team_score == $away_team_score;

                    $final_result = "";
                    $predict_result = "";


                    if ($home_team_score > $away_team_score) {
                        $final_result = "home_team_win";
                    }
                    if ($home_team_score < $away_team_score) {
                        $final_result = "home_team_lose";
                    }
                    if ($home_team_score == $away_team_score) {
                        $final_result = "draw";
                    }

                    if ($home_team_predict > $away_team_predict) {
                        $predict_result = "home_team_win";
                    }
                    if ($home_team_predict < $away_team_predict) {
                        $predict_result = "home_team_lose";
                    }
                    if ($home_team_predict == $away_team_predict) {
                        $predict_result = "draw";
                    }


                    if ($final_result == $predict_result) {
                        $total_pts = $total_pts + $options->win_lose_draw_pts;
                        $pts_breakdown_outcome = $options->win_lose_draw_pts;
                    }

                    // calculate goal different pts

                    $goal_different = $home_team_score - $away_team_score;
                    $goal_different_predict = $home_team_predict - $away_team_predict;

                    if ($goal_different == $goal_different_predict) {
                        $total_pts = $total_pts +  $options->goal_difference_pts;
                        $pts_breakdown_goal_diff = $options->goal_difference_pts;
                    }

                    // calculate team goal pts
                    if ($home_team_predict == $home_team_score) {
                        $total_pts = $total_pts +  $options->home_goals_pts;
                        $pts_breakdown_home_goal = $options->home_goals_pts;
                    }

                    if ($away_team_predict == $away_team_score) {
                        $total_pts = $total_pts +  $options->away_goals_pts;
                        $pts_breakdown_away_goal = $options->away_goals_pts;
                    }

                    // two x booster pts

                    if ($prediction->twox_booster == 1) {
                        $used_2x_booster = true;
                        $total_pts = $total_pts * $options->twox_booster_pts;
                        $pts_breakdown_boost =  $options->twox_booster_pts;
                    }
                }
            }

            $breakdown = [
                        'outcome' => $pts_breakdown_outcome ? $pts_breakdown_outcome : 0,
                        'goal_diff' => $pts_breakdown_goal_diff ? $pts_breakdown_goal_diff : 0,
                        'home_goal' => $pts_breakdown_home_goal ? $pts_breakdown_home_goal : 0,
                        'away_goal' => $pts_breakdown_away_goal ? $pts_breakdown_away_goal : 0,
                        'boost' => $pts_breakdown_boost,
                        'total' => $total_pts
                    ];
        }

        $datetime = new DateTime($this['kickoff_time']);

        $arrayData = [
            'used_booster' => $used_2x_booster,
            'code' => $this['code'],
            'event' => $this['event'],
            'finished' => $this['finished'],
            'finished_provisional' => $this['finished_provisional'],
            'id' => $this['id'],
            'kickoff_time' => $datetime->format(DateTime::ATOM),
            'minutes' => $this['minutes'],
            'provisional_start_time' => $this['provisional_start_time'],
            'started' => $this['started'],
            'team_a' => $this->getTeam($this['team_a'], 'team_a'),
            'team_a_score' => $this['team_a_score'],
            'team_h' => $this->getTeam($this['team_h'], 'team_h'),
            'team_h_score' => $this['team_h_score'],
            'prediction' => new PredictionResource($prediction),
            'result_pts' => $total_pts,
            'breakdown' =>  $breakdown
        ];

        return $arrayData;
    }
}
