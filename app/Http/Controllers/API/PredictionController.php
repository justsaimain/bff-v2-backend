<?php

namespace App\Http\Controllers\API;

use App\Models\Prediction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PredictionController extends Controller
{
    public function postPrediction(Request $request)
    {
        $request->validate([
            'event' => 'required',
            'id' => 'required',
            'home_team_goal' => 'required',
            'away_team_goal' => 'required'
        ]);

        $checkExisting = Prediction::where('user_id', Auth::id())
            ->where('fixture_event', $request->event)
            ->where('fixture_id', $request->id)
            ->first();

        if ($checkExisting) {
            $checkExisting->team_h_goal = $request->home_team_goal;
            $checkExisting->team_a_goal = $request->away_team_goal;
            $checkExisting->update();
            $return_data = $checkExisting;
        } else {
            $prediction = new Prediction();
            $prediction->user_id = Auth::id();
            $prediction->fixture_id = $request->id;
            $prediction->fixture_event = $request->event;
            $prediction->team_h_goal = $request->home_team_goal;
            $prediction->team_a_goal = $request->away_team_goal;
            $prediction->save();

            $return_data = $prediction;
        }

        return response()->json([
            'success' => true,
            'flag' => 'prediction_created',
            'message' => 'Prediction Successfully Created',
            'data' => json_decode($return_data),
            'extra' => null,
        ], 200);
    }

    public function getPredictionList(Request $request)
    {
        if ($request->input('gw')) {
            $predictions = Prediction::where('fixture_event', $request->input('gw'))->where('user_id', Auth::id())->get();
        } else {
            $predictions = Auth::user()->predictions;
        }

        return response()->json([
            'success' => true,
            'flag' => 'prediction_list',
            'message' => 'Prediction List',
            'data' => $predictions,
            'extra' => null,
        ], 200);
    }
}
