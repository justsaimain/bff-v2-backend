<?php

namespace App\Http\Controllers\Api;

use App\Models\Option;
use App\Models\Prediction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
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


    public function getFixtureResult($fixture)
    {
        $cacheData = Cache::get('leaderboard_fixtures__data__cache');
        if ($cacheData) {
            $fixtures = $cacheData;
        } else {
            $fixtures = Http::withHeaders([
                'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
                'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
            ])->get(config('url.fixtures'), [
                'gw' => $fixture->fixture_event,
            ])->json();
            Cache::put('leaderboard_fixtures__data__cache', $fixtures, now()->addMinutes());
        }

        $fixture_collection = collect($fixtures);
        $filtered = $fixture_collection->filter(function($item) use ($fixture){
            return $item["finished"] === true && $item["id"] === (int) $fixture->fixture_id;
        })->values();

        if(count($filtered) > 0){
            return [
                "team_a_score" =>  $filtered[0]['team_a_score'],
                "team_h_score" => $filtered[0]['team_h_score']
            ];
        }else{
            return [
                "team_a_score" => 0,
                "team_h_score" => 0
            ];
        }
    }

    public function __invoke()
    {
        $user = Auth::guard('api')->user();
        $current_gameweek = Option::first()->current_gameweek;
        $options = Option::first();
        $user_score_list = [];
        $your_score_list = [];
        $used_twox_booster_your_score = 0;
        $used_twox_booster_high_score = 0;
        $deadline = [];

        $cacheData = Cache::get('leaderboard_fixtures__data__cache');

        if ($cacheData) {
            $fixtures = $cacheData;
        } else {
            $fixtures = Http::withHeaders([
                'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
                'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
            ])->get(config('url.fixtures'), [
                'gw' => $current_gameweek,
            ])->json();
            Cache::put('leaderboard_fixtures__data__cache', $fixtures, now()->addMinutes(3));
        }

        $fixture_collection = collect($fixtures);
        $filtered_upcoming_matches = $fixture_collection->filter(function($item){
            return $item["finished"] === false;
        })->values();

        $first_upcoming_match = $filtered_upcoming_matches[0];

        $first_upcoming_match_full_date =  Carbon::parse($first_upcoming_match['kickoff_time'])->subMinutes(30);

        $different_from_full_date = Carbon::now()->diff($first_upcoming_match_full_date, false);

        $deadline = [
            "days" => $different_from_full_date->d,
            "hours" => $different_from_full_date->h,
            "minutes" => $different_from_full_date->i
        ];


        $recent_matchs = [];
        $matchs = array_reverse($fixtures);

        foreach ($matchs as $key => $match){
            if ($match['finished'] === true){
             if(count($recent_matchs) <= 5){
                array_push($recent_matchs , [
                    "id" => $match["id"],
                    "team_a" => [
                        ...$this->getTeam($match['team_a'], 'team_a'),
                        "score" => $match['team_a_score']
                    ],
                    "team_h" => [
                        ...$this->getTeam($match['team_h'], 'team_h'),
                        "score" => $match['team_h_score']
                    ]
                ]);
             }
            }
        }


        $predictions = Prediction::where('fixture_event', $current_gameweek)->orderBy("twox_booster","desc") ->get();


        foreach ($predictions as $prediction) {
            $temp_score_list = [];
            $fixture_result = $this->getFixtureResult($prediction);

            $final_result = "";
            $predict_result = "";

            $home_team_predict = $prediction->team_h_goal['value'];
            $away_team_predict = $prediction->team_a_goal['value'];
            $home_team_score = $fixture_result['team_h_score'];
            $away_team_score = $fixture_result['team_a_score'];
            if ($home_team_score > $away_team_score) {
                $final_result = "home_team_win";
            }else if ($home_team_score < $away_team_score) {
                $final_result = "home_team_lose";
            }else if ($home_team_score == $away_team_score) {
                $final_result = "draw";
            }

            if ($home_team_predict > $away_team_predict) {
                $predict_result = "home_team_win";
            }else if ($home_team_predict < $away_team_predict) {
                $predict_result = "home_team_lose";
            }else if ($home_team_predict == $away_team_predict) {
                $predict_result = "draw";
            }

            if ($final_result === $predict_result) {
                array_push($temp_score_list , $options->win_lose_draw_pts);
            }

            $goal_different = abs($home_team_score - $away_team_score);
            $goal_different_predict = abs($home_team_predict - $away_team_predict);

            if ($goal_different === $goal_different_predict) {
                array_push($temp_score_list , $options->goal_difference_pts);
            }

            if ($home_team_predict === $home_team_score) {
                array_push($temp_score_list ,  $options->home_goals_pts);
            }

            if ($away_team_predict === $away_team_score) {
                array_push($temp_score_list , $options->away_goals_pts);
            }

            $final_temp_score = array_sum($temp_score_list);

            if ($prediction->twox_booster === 1) {
                if($used_twox_booster_high_score !== 1) {
                   $final_temp_score = $final_temp_score * $options->twox_booster_pts;
                    // array_push($temp_score_list ,array_sum($temp_score_list) * $options->twox_booster_pts);
                    $used_twox_booster_high_score = 1;
                }
            }

           array_push($user_score_list, ['id' => $prediction->user_id ,'user'=> $prediction->user, 'pts' => $final_temp_score]);

        }

        if ($user) {
            $your_prediction = Prediction::where('user_id', $user->id)
                        ->where('fixture_event', $current_gameweek)
                        ->get();



        foreach ($your_prediction as $prediction) {

            $temp_score_list = [];
            $fixture_result = $this->getFixtureResult($prediction);

            $final_result = "";
            $predict_result = "";

            $home_team_predict = $prediction->team_h_goal['value'];
            $away_team_predict = $prediction->team_a_goal['value'];
            $home_team_score = $fixture_result['team_h_score'];
            $away_team_score = $fixture_result['team_a_score'];


            if ($home_team_score > $away_team_score) {
                $final_result = "home_team_win";
            }else if ($home_team_score < $away_team_score) {
                $final_result = "home_team_lose";
            }else if ($home_team_score == $away_team_score) {
                $final_result = "draw";
            }

            if ($home_team_predict > $away_team_predict) {
                $predict_result = "home_team_win";
            }else if ($home_team_predict < $away_team_predict) {
                $predict_result = "home_team_lose";
            }else if ($home_team_predict == $away_team_predict) {
                $predict_result = "draw";
            }

            if ($final_result === $predict_result) {
                array_push($temp_score_list , $options->win_lose_draw_pts);
            }

            $goal_different = abs($home_team_score - $away_team_score);
            $goal_different_predict = abs($home_team_predict - $away_team_predict);

            if ($goal_different === $goal_different_predict) {
                array_push($temp_score_list , $options->goal_difference_pts);
            }

            if ($home_team_predict === $home_team_score) {
                array_push($temp_score_list ,  $options->home_goals_pts);
            }

            if ($away_team_predict === $away_team_score) {
                array_push($temp_score_list , $options->away_goals_pts);
            }

            $final_temp_score = array_sum($temp_score_list);

            if ($prediction->twox_booster === 1) {
                if($used_twox_booster_your_score !== 1) {
                   $final_temp_score = $final_temp_score * $options->twox_booster_pts;
                    $used_twox_booster_your_score = 1;
                }
            }

           array_push($your_score_list , $final_temp_score);
        }
        }

        $result = array();
        foreach ($user_score_list as $k => $v) {
            $id = $v['id'];
            $result[$id]['pts'][] = $v['pts'];
            $result[$id]['user'] = $v['user'];
        }


        $filtered_score_list = array();
        foreach ($result as $key => $value) {
            $filtered_score_list[] = array('id' => $key, 'pts' => array_sum($value['pts']) , 'user' => $value['user']);
        }



        $pts_list = array_column($filtered_score_list, 'pts');
        $max_pts_index = count($pts_list) > 0 ? array_keys($pts_list, max($pts_list)) : 0;

        return response()->json([
            'success' => true,
            'flag' => 'home_page_data',
            'message' => 'Home Page Data',
            'data' => [
                'user' => $user,
                'current_gameweek' => $current_gameweek,
                'your_score' => array_sum($your_score_list),
                'avg_score' => count($filtered_score_list) > 0 ? round(array_sum(array_column($filtered_score_list, 'pts')) / count($filtered_score_list)) : 0,
                'highest_score' => count($pts_list) > 0 ? max($pts_list) : 1,
                'top_predictor' => count($filtered_score_list) > 0 ? $filtered_score_list[$max_pts_index[0]] : null,
                'recent_matchs' => $recent_matchs,
                'deadline' => $deadline
                ]
        ], 200);
    }
}
