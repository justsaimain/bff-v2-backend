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
    public function getFixtureResult($fixture)
    {
        // $cacheData = Cache::get('leaderboard_fixtures__data__cache');
        // if ($cacheData) {
        //     $fixtures = $cacheData;
        // } else {
            $fixtures = Http::withHeaders([
                'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
                'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
            ])->get(config('url.fixtures'), [
                'gw' => $fixture->fixture_event,
            ])->json();
            // Cache::put('leaderboard_fixtures__data__cache', $fixtures, now()->addSeconds(5));
        // }

        $fixture_collection = collect($fixtures);
        $filtered = $fixture_collection->filter(function ($item) use ($fixture) {
            return $item["finished"] === true && $item["id"] === (int) $fixture->fixture_id;
        })->values();


        if (count($filtered) > 0) {
            return [
                "team_h" => $filtered[0]['team_h'],
                "team_a" => $filtered[0]['team_a'],
                "team_a_score" =>  $filtered[0]['team_a_score'],
                "team_h_score" => $filtered[0]['team_h_score']
            ];
        } else {
            return [
                "team_h" => null,
                "team_a" => null,
                "team_a_score" => null,
                "team_h_score" => null
            ];
        }
    }

    public function __invoke(Request $request)
    {
        $arrayData = [];
        $gameweek = $request->input('gw');
        $of_user = $request->input('user');
        if ($of_user) {
            $predictions = Prediction::with('user')->where('fixture_event', $gameweek)->where('user_id', $of_user)->get();
        } else {
            $predictions = Prediction::with('user')->where('fixture_event', $gameweek)->get();
        }


        $options = Option::first();
        // $cacheData = Cache::get('leaderboard_fixtures__data__cache');
        // if ($cacheData) {
        //     $fixtures = $cacheData;
        // } else {
            // $response =  Http::withHeaders([
            //     'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
            //     'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
            // ])->get('https://fantasy-premier-league3.p.rapidapi.com/fixtures');
            // $fixtures = $response->json();
            // Cache::put('leaderboard_fixtures__data__cache', $fixtures, now()->addMinutes(20));
        // }




        foreach ($predictions as $key => $prediction) {
            $total_pts = 0;
            $point_logs = [];
            $fixture_logs = [];
            $home_team_predict = $prediction->team_h_goal['value'];
            $away_team_predict = $prediction->team_a_goal['value'];
            $fixture_result = $this->getFixtureResult($prediction);

            if ($fixture_result['team_h_score'] === null && $fixture_result['team_a_score'] === null) {
                $prediction['total_pts'] = 0;
            } else {
                $final_result = "";
                $predict_result = "";

                $home_team_predict = $prediction->team_h_goal['value'];
                $away_team_predict = $prediction->team_a_goal['value'];
                $home_team_score = $fixture_result['team_h_score'];
                $away_team_score = $fixture_result['team_a_score'];
                $home_team = $fixture_result['team_h'];
                $away_team = $fixture_result['team_a'];
                if ($home_team_score > $away_team_score) {
                    $final_result = "home_team_win";
                } elseif ($home_team_score < $away_team_score) {
                    $final_result = "home_team_lose";
                } elseif ($home_team_score == $away_team_score) {
                    $final_result = "draw";
                }

                if ($home_team_predict > $away_team_predict) {
                    $predict_result = "home_team_win";
                } elseif ($home_team_predict < $away_team_predict) {
                    $predict_result = "home_team_lose";
                } elseif ($home_team_predict == $away_team_predict) {
                    $predict_result = "draw";
                }


                if ($final_result === $predict_result) {
                    // echo "#same = " .  $options->win_lose_draw_pts;
                    array_push($point_logs, ['Outcome (win/draw/lose)' => $options->win_lose_draw_pts]);
                    $total_pts = $total_pts + $options->win_lose_draw_pts;
                }

                // calculate goal different pts

                $goal_different = $home_team_score - $away_team_score;
                $goal_different_predict = $home_team_predict - $away_team_predict;


                if ($goal_different === $goal_different_predict) {
                    // echo "#dff = " .  $options->goal_difference_pts;
                    array_push($point_logs, ['Goal difference' => $options->goal_difference_pts]);
                    $total_pts = $total_pts +  $options->goal_difference_pts;
                }

                // calculate team goal pts
                if ($home_team_predict === $home_team_score) {
                    // echo "#home = " .  $options->home_goals_pts;
                    array_push($point_logs, ['Home goals' => $options->home_goals_pts]);
                    $total_pts = $total_pts +  $options->home_goals_pts;
                }

                if ($away_team_predict === $away_team_score) {
                    // echo "#away = " .  $options->away_goals_pts;
                    array_push($point_logs, ['Away goals' => $options->away_goals_pts]);
                    $total_pts = $total_pts +  $options->away_goals_pts;
                }

                // two x booster pts

                if ($prediction->twox_booster === 1) {
                    // echo "#before boosted  bx2= " . $total_pts;
                    // echo "#boosted  x= " .  $options->twox_booster_pts;
                    array_push($point_logs, ['Before 2x Boosted' => $total_pts]);
                    array_push($point_logs, ['After 2x Boosted' => $total_pts * $options->twox_booster_pts]);
                    $total_pts = $total_pts * $options->twox_booster_pts;
                }

                $prediction['total_pts'] = $total_pts;
                $prediction['point_logs'] = $point_logs;
                $prediction['fixture_logs'] = [
                    'team_h_predict' => $home_team_predict,
                    'team_a_predict' => $away_team_predict,
                    'team_h_score' => $home_team_score,
                    'team_a_score' => $away_team_score,
                    'twox_booster' => $prediction->twox_booster,
                    'result_pts' => $total_pts
                ];
                $prediction['team_a'] = $away_team;
                $prediction['team_h'] = $home_team;

                array_push($arrayData, $prediction);
            }
        }


        $result = array();
        foreach ($arrayData as $k => $v) {
            $id = $v['user']['id'];
            $result[$id]['pts'][] = $v['total_pts'];
            $result[$id]['point_logs'][strval($v['team_h']) . " vs " . strval($v['team_a']) ] = $v['point_logs'];
            $result[$id]['fixture_logs'][strval($v['team_h']) . " vs " . strval($v['team_a']) ] = $v['fixture_logs'];
            $result[$id]['user'] = $v['user'];
        }


        $new = array();


        foreach ($result as $key => $value) {
            $new[] = array('user' => $value['user'], 'total_pts' => array_sum($value['pts']) , 'point_logs' => $value['point_logs'] , 'fixture_logs' => $value['fixture_logs']);
        }

        $collectData = collect($new);
        $sortedData = $collectData->sortByDesc('total_pts');
        return response()->json([
            'success' => true,
            'flag' => 'leaderboard',
            'message' => 'Get Leaderboard List',
            'data' => $sortedData->values()->filter(function ($value) {
                return $value['total_pts'] > 0;
            })
        ], 200);
    }
}
