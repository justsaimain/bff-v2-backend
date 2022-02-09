<?php

namespace App\Http\Controllers\API;

use App\Models\Option;
use App\Models\Prediction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $gameweek = $request->input('gw');
        $predictions = Prediction::with('user')->where('fixture_event', $gameweek)->get();
        $options = Option::first();
        $cacheData = Cache::get('leaderboard_fixtures__data__cache');
        $arrayData = [];
        if ($cacheData) {
            $fixtures = $cacheData;
        } else {
            $response =  Http::withHeaders([
                'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
                'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
            ])->get('https://fantasy-premier-league3.p.rapidapi.com/fixtures');
            $fixtures = $response->json();
            Cache::put('leaderboard_fixtures__data__cache', $fixtures, now()->addMinutes(3));
        }


        foreach ($predictions as $prediction) {
            $total_pts = 0;

            $home_team_predict = $prediction->team_h_goal['value'];
            $away_team_predict = $prediction->team_a_goal['value'];
            $current_fixture = null;
            foreach ($fixtures as $key => $value) {
                if ($value['id'] == $prediction->fixture_id) {
                    $current_fixture = $fixtures[$key];
                }
            }

            if ($current_fixture['finished'] == true) {
                $home_team_score = $current_fixture['team_h_score'];
                $away_team_score = $current_fixture['team_a_score'];


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
                }

                // calculate goal different pts

                $goal_different = abs($home_team_score - $away_team_score);
                $goal_different_predict = abs($home_team_predict - $away_team_predict);

                if ($goal_different == $goal_different_predict) {
                    $total_pts = $total_pts +  $options->goal_difference_pts;
                }

                // calculate team goal pts
                if ($home_team_predict == $home_team_score) {
                    $total_pts = $total_pts +  $options->home_goals_pts;
                }

                if ($away_team_predict == $away_team_score) {
                    $total_pts = $total_pts +  $options->away_goals_pts;
                }

                // two x booster pts

                if ($prediction->twox_booster == 1) {
                    $total_pts = $total_pts * $options->twox_booster_pts;
                }

                $prediction['total_pts'] = $total_pts;

                array_push($arrayData, $prediction);
            }
        }

        usort($arrayData, function ($a, $b) {
            if ($a["total_pts"] == $b["total_pts"]) {
                return (0);
            }
            return (($a["total_pts"] > $b["total_pts"]) ? -1 : 1);
        });


        return response()->json([
            'success' => true,
            'flag' => 'leaderboard',
            'message' => 'Get Leaderboard List',
            'data' => $arrayData
        ], 200);
    }
}
