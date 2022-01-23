<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Http;

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
}
