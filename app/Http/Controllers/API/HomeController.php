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


    public function __invoke()
    {
        $user = Auth::guard('api')->user();
        $current_gameweek = Option::first()->current_gameweek;
        $your_score  = 0;
        $predict_user_count = 0;
        $options = Option::first();
        $predict_user_scores = 0;
        $user_score_list = [];
        $c_fixture_list = [];
        $your_score_list = [];
        $used_twox_booster = 0;


        $fixtures = Http::withHeaders([
            'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
            'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
        ])->get(config('url.fixtures'), [
            'gw' => $current_gameweek,
        ])->json();

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
        $predict_user_count = $predictions->count();

        foreach ($predictions as $prediction) {
            $p_fixture = null;
            foreach ($fixtures as $key => $value) {
                if ($value['id'] == $prediction->fixture_id) {
                    $p_fixture = $fixtures[$key];
                    break;
                }
            }

            if ($p_fixture['finished'] == true) {
                $p_score = 0;
                $home_team_predict = $prediction->team_h_goal['value'];
                $away_team_predict = $prediction->team_a_goal['value'];
                $home_team_score = $p_fixture['team_h_score'];
                $away_team_score = $p_fixture['team_a_score'];

                // calculate win lose draw point > +3 pts

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
                    $predict_user_scores = $predict_user_scores + $options->win_lose_draw_pts;
                    $p_score = $p_score + $options->win_lose_draw_pts;
                }

                // calculate goal different pts

                $goal_different = abs($home_team_score - $away_team_score);
                $goal_different_predict = abs($home_team_predict - $away_team_predict);

                if ($goal_different == $goal_different_predict) {
                    $predict_user_scores = $predict_user_scores +  $options->goal_difference_pts;
                    $p_score =  $p_score + $options->goal_difference_pts;
                }

                // calculate team goal pts
                if ($home_team_predict == $home_team_score) {
                    $predict_user_scores = $predict_user_scores +  $options->home_goals_pts;
                    $p_score = $p_score + $options->home_goals_pts;
                }

                if ($away_team_predict == $away_team_score) {
                    $predict_user_scores = $predict_user_scores +  $options->away_goals_pts;
                    $p_score = $p_score + $options->away_goals_pts;
                }

                // two x booster pts

                if ($prediction->twox_booster == 1) {
                    $predict_user_scores = $predict_user_scores * $options->twox_booster_pts;
                    $p_score = $p_score + $options->twox_booster_pts;
                }

                array_push($user_score_list, ['id' => $prediction->user_id ,'user'=> $prediction->user, 'pts' => $p_score]);
            }
        }

        if ($user) {
            $your_prediction = Prediction::where('user_id', $user->id)
                        ->where('fixture_event', $current_gameweek)
                        ->get();

            foreach ($your_prediction as $prediction) {
                $c_fixture = null;
                foreach ($fixtures as $key => $value) {
                    if ($value['id'] == $prediction->fixture_id) {
                     array_push($c_fixture_list,$fixtures[$key]);
                    }
                }


                foreach($c_fixture_list as $key => $value){
                    if ($c_fixture_list[$key]['finished'] == true) {
                        $home_team_predict = $prediction->team_h_goal['value'];
                        $away_team_predict = $prediction->team_a_goal['value'];
                        $home_team_score = $c_fixture_list[$key]['team_h_score'];
                        $away_team_score = $c_fixture_list[$key]['team_a_score'];

                        // calculate win lose draw point > +3 pts

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
                            array_push($your_score_list , $options->win_lose_draw_pts);
                            // $your_score = $your_score + $options->win_lose_draw_pts;
                        }

                        // return $your_score;

                        // calculate goal different pts

                        $goal_different = abs($home_team_score - $away_team_score);
                        $goal_different_predict = abs($home_team_predict - $away_team_predict);

                        if ($goal_different == $goal_different_predict) {
                            // $your_score = $your_score +  $options->goal_difference_pts;
                            array_push($your_score_list , $options->goal_difference_pts);

                        }

                        // calculate team goal pts
                        if ($home_team_predict == $home_team_score) {
                            // $your_score = $your_score +  $options->home_goals_pts;
                            array_push($your_score_list , $options->home_goals_pts);

                        }

                        if ($away_team_predict == $away_team_score) {
                            // $your_score = $your_score +  $options->away_goals_pts;
                            array_push($your_score_list , $options->away_goals_pts);

                        }

                        // two x booster pts

                        if ($prediction->twox_booster == 1) {
                            // return $prediction;
                            // $your_score = $your_score * $options->twox_booster_pts;

                            // need to checkhere

                            if($used_twox_booster !== 1) {
                                array_push($your_score_list ,array_sum($your_score_list) * $options->twox_booster_pts);
                                $used_twox_booster = 1;
                            }

                        }
                    }
                }

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
                'avg_score' => count($filtered_score_list) > 0 ? round($predict_user_scores / count($filtered_score_list)) : 0,
                'highest_score' => count($pts_list) > 0 ? max($pts_list) : 1,
                'top_predictor' => count($filtered_score_list) > 0 ? $filtered_score_list[$max_pts_index[0]] : null,
                'recent_matchs' => $recent_matchs
                ]
        ], 200);
    }
}
