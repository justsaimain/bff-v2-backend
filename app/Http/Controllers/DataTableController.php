<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Option;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DataTableController extends Controller
{
    public function findTeamDetail($id)
    {
        $cacheDataTeam = Cache::get('teams__data__cache');

        if ($cacheDataTeam) {
            $teamData = $cacheDataTeam;
        } else {
            $responseTeam =  Http::withHeaders([
                'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
                'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
            ])->get('https://fantasy-premier-league3.p.rapidapi.com/teams/simple');
            $teamData = $responseTeam->json();
            Cache::put('teams__data__cache', $teamData, now()->addDays(15));
        }

        foreach ($teamData as $team) {
            if ($id == $team['id']) {
                return $team;
            }
        }
    }

    public function getUsers()
    {
        $users = User::query();
        return Datatables::of($users)
            ->addIndexColumn()
            ->addColumn('action', function ($user) {
                $html = '<a type="button" class="btn btn-xs waves-effect waves-light btn-primary mr-1" href="' . route('users.show', $user->id) . '">View</a>';
                return $html;
            })->make(true);
    }

    public function getFixtures()
    {
        $cacheData = Cache::get('fixtures__data__cache');

        if ($cacheData) {
            $data = $cacheData;
        } else {
            $response =  Http::withHeaders([
                'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
                'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
            ])->get('https://fantasy-premier-league3.p.rapidapi.com/fixtures');
            $data = $response->json();
            Cache::put('fixtures__data__cache', $data, now()->addMinutes(15));
        }

        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('event', function ($data) {
                if ($data['event'] == null) {
                    return '';
                }
                return 'Gameweek ' . $data['event'];
            })
            ->editColumn('team_h', function ($data) {
                $teamDetail = $this->findTeamDetail($data['team_h']);
                return $teamDetail['name'];
            })
            ->editColumn('team_a', function ($data) {
                $teamDetail = $this->findTeamDetail($data['team_a']);
                return $teamDetail['name'];
            })
            ->addColumn('result', function ($data) {
                return '<span class="badge badge-primary" style="font-size:12px;">' . $data['team_h_score'] . '</span> - <span class="badge badge-primary" style="font-size:12px;">' . $data['team_a_score'] . '</span>';
            })
            ->editColumn('kickoff_time', function ($data) {
                return Carbon::parse($data['kickoff_time'])->format('d M Y - h:i A');
            })
            ->addColumn('action', function ($data) {
                $html = '<a type="button" class="btn btn-xs waves-effect waves-light btn-primary mr-1" href="/">View</a>';
                return $html;
            })->rawColumns(['finished', 'started', 'action', 'result'])->make(true);
    }

  
    public function getPredictions()
    {
        $predictions = Prediction::with('user')->get();
        $options = Option::first();


        $cacheData = Cache::get('predictoin_fixtures__data__cache');

        if ($cacheData) {
            $fixtures = $cacheData;
        } else {
            $response =  Http::withHeaders([
            'x-rapidapi-host' => 'fantasy-premier-league3.p.rapidapi.com',
            'x-rapidapi-key' => 'abe4621a9bmshbc1c9a211f870d6p157512jsnd3bbdf64de8b'
        ])->get('https://fantasy-premier-league3.p.rapidapi.com/fixtures');
            $fixtures = $response->json();
            Cache::put('predictoin_fixtures__data__cache', $fixtures, now()->addMinutes(3));
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
            }
        }

        return Datatables::of($predictions)
            ->addIndexColumn()
            ->editColumn('total_pts', function ($data) {
                if ($data['total_pts']) {
                    return '<span class="badge badge-success" style="font-size:12px;"> + ' . $data['total_pts'] . '</span>';
                }
                return '<small>Not Finished</small>';
            })
            ->editColumn('fixture_event', function ($data) {
                return 'GW' . $data['fixture_event'];
            })
             ->editColumn('updated_at', function ($data) {
                 return $data['updated_at']->format('d M Y - h:i A');
             })
            ->addColumn('action', function ($data) {
                $html = '<a type="button" class="btn btn-xs waves-effect waves-light btn-primary mr-1" href="/predictions/'.$data['id'].'">View</a>';
                return $html;
            })->rawColumns(['updated_at','total_pts','action'])->make(true);
    }
}
